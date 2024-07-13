<?php

namespace App\Livewire\AiReply;

use Livewire\Component;

use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailboxConnectForm extends Component
{

    public $host = 'mail.fabiopacifici.com';
    public $port = '993';
    public $encryption = 'ssl';
    public $username;
    public $password;
    public $filter = 'day'; // Default filter
    public $reply = [];
    public $message;
    public $messages = [];
    public $fetching = false;


    protected $rules = [
        'host' => 'required',
        'port' => 'required|numeric',
        'encryption' => 'required',
        'username' => 'required|email',
        'password' => 'required',
    ];



    public function render()
    {
        return view('livewire.ai-reply.mailbox-connect-form');
    }

    public function boot(){
        $this->username = env('MAIL_FROM_ADDRESS');
    }

    public function mount()
    {

        $this->connectMailbox();
        $this->password = env('AI_MAIL_PASSWORD');
    }


    public function updated($property)
    {
        if ($property === 'filter') {
            $this->fetching = true;
            $this->connectMailbox();
        }
    }

    public function connectMailbox()
    {
        //$this->validate();
        $this->fetching = true;
        // Logic to establish IMAP connection and verify credentials
        $mailbox = new Mailbox(
            '{' . $this->host . ':' . $this->port . '/imap/ssl}INBOX', // IMAP server and mailbox folder
            $this->username, // Username for the before configured mailbox
            env('AI_MAIL_PASSWORD'), // Password for the before configured username
            storage_path('app'), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            true // Attachment filename mode (optional; false = random filename; true = original filename)
        );
        //dd($mailbox);


        try {
            // Get all emails (messages)
            // PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
            $date = Carbon::now()->subDays($this->getDays())->format('Ymd');
            //dd($date);
            $criteria = 'SINCE "' . $date . '"';
            //dd($criteria);
            $mailsIds = $mailbox->searchMailbox($criteria);
            //dd($mailsIds);
        } catch (ConnectionException $ex) {
            $message = "IMAP connection failed: " . implode(",", $ex->getErrors('all'));
            Log::error($message);
            return back()->with('message', $message);
        }

        //dd($mailsIds);
        // If $mailsIds is empty, no emails could be found
        if (!$mailsIds) {
            //dd('here');
            Log::info('Mailbox is empty');

            return to_route('dashboard')->with('message', 'Mailbox is empty');
        }

        $this->getEmailMessages($mailbox, $mailsIds);
        //dd($this->messages);
        $this->fetching = false;
        $mailbox->disconnect();
    }

    private function getEmailMessages($mailbox, $mailsIds)
    {
        // Put the latest email on top of listing
        rsort($mailsIds);

        // Get the last 15 emails only
        array_splice($mailsIds, 15);

        // Loop through emails one by one

        $this->messages = array_map(function ($num) use ($mailbox) {

            //dd($mailbox);
            $head = $mailbox->getMailHeader($num);
            //dd($head);
            $markAsSeen = false;
            $mail = $mailbox->getMail($num, $markAsSeen);
            $message = [
                'messageId' => $head->messageId,
                'isSeen' => $head->isSeen,
                'isAnswered' => $head->isAnswered,
                'isRecent' => $head->isRecent,
                'isFlagged' => $head->isFlagged,
                'isDeleted' => $head->isDeleted,
                'isDraft' => $head->isDraft,
                'subject' => $head->subject,
                'from' => $head->fromAddress,
                'sender' => isset($head->fromName) ? $head->fromName : '',
                'replyToAddresses' => array_keys($head->replyTo),
                'date' => $head->date,
                'content' => $mail->textHtml ? $mail->textHtml : $mail->textPlain
                // Add more fields as needed
            ];
            //dd($message);
            return $message;
        }, $mailsIds);
    }


    public function getAnswer($id)
    {

        Log::info('looking for the message by its id' . $id);
        foreach ($this->messages as $message) {
            if (in_array($id, $message)) {

                $this->message = $message;
                break;
            }
        }

        //dd($this->message);
        // prepare the payload
        $payload = [
            'model' => 'llama3:latest',
            'stream' => false,
            'messages' => [

                [
                    'role' => 'system',
                    'content' => "
                    You are FabiA. You are Fabio Pacifici's personal assistant.
                    You manage Fabio's inbox and reply to incoming messages.
                    You are provided with the message resourse as json object.
                    Your task is to formulate a reply in the same language of the sender.
                    You are friendly and professional.
                    If a reply requires Fabio's instructions simply inform the sender that the enquiry has been forwarded to Fabio and that a response might
                    be provided at a later time if necessary.
                    Sign your messages in the following way: 'Regards, FabiA (Fabio Pacifici Assistant)'.
                    "
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->message)
                ]
            ]
        ];

        //dd($payload);
        $response = Http::post('http://127.0.0.1:11434/api/chat', $payload);


        $response->onError(function ($message) {
            Log::error('❌ Error: ' . $message);
            exit(1);
        });

        //dd($response->json());
        Log::info('This is the ollama response', ['response' => $response->json()]);
        // the reply message array
        $this->reply = $response->json(); // Get the response

    }

    private function getDays()
    {
        return match ($this->filter) {
            'day' => 1,
            'week' => 7,
            'month' => 30,
            default => 1,
        };
    }
}
