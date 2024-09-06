<?php

namespace App\Livewire\AiReply;

use Livewire\Component;

use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PDO;

class MailboxConnectForm extends Component
{

    // mailbox connector settings
    public $host;
    public $port = '993';
    public $encryption = 'ssl';
    public $username;
    public $password;
    public $filter = 'day'; // Default filter
    // messages processing properties
    public $reply = [];
    public $message;
    public $messages = [];
    public $fetching = false;
    // model processor properties
    public $models;
    public $selectedModel;
    public $assistantSystem;
    public $ollamaServerAddress;

    // validation
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

    public function boot()
    {
        // mailbox
        $this->username = config('responder.imap.username');
        $this->host = config('responder.imap.server');
        // ollama server settings
        $this->selectedModel = config('responder.assistant.model');
        $this->assistantSystem = config('responder.assistant.system');
        $this->models = $this->getModels();
        $this->ollamaServerAddress = config('responder.assistant.server');
        //dd($this->models);
    }

    public function mount()
    {
        $this->password = config('responder.imap.password');
    }


    public function updated($property)
    {
        if ($property === 'filter') {
            $this->fetching = true;
            $this->connectMailbox();
        }
    }

    public function getModels(){
        $response = Http::get(config('responder.assistant.tags'));
        return $response->json();

    }

    public function connectMailbox()
    {
        //$this->validate();
        $this->fetching = true;
        // Logic to establish IMAP connection and verify credentials
        $mailbox = new Mailbox(
            '{' . $this->host . ':' . $this->port . '/imap/ssl}INBOX', // IMAP server and mailbox folder
            $this->username, // Username for the before configured mailbox
            $this->password, // Password for the before configured username
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
            Log::info("Fetching emails date: $date");
            //dd($date);
            $criteria = 'SINCE "' . $date . '"';
            Log::info("Fetching emails criteria: $criteria");
            //dd($criteria);
            $mailsIds = $mailbox->searchMailbox($criteria);
            //dd($mailsIds);
            Log::info("Fetching emails ids: ". count($mailsIds));
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
            Log::info("Fetching email: $num");
            Log::info("Fetching email header: $head");
            $markAsSeen = true;
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

    /**
     * generates reply for a givem message
     *
     * @param string $messageId the id of the message retrieved from the imap server
     * @return void
     */
    public function generateReplyFor($messageId): void
    {

        $this->setMessage($messageId);

        //dd($this->message);
        // prepare the payload
        $payload = [
            'model' => $this->selectedModel,
            'stream' => false,
            'messages' => [

                [
                    'role' => 'system',
                    'content' => $this->assistantSystem
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->message)
                ]
            ]
        ];

        //dd($payload);
        $response = Http::post($this->ollamaServerAddress, $payload);


        $response->onError(function ($message) {
            Log::error('❌ Error: ' . $message);
            exit(1);
        });

        //dd($response->json());
        Log::info('This is the ollama response', ['response' => $response->json()]);
        // the reply message array
        $this->reply = $response->json(); // Get the response

    }


    public function setMessage($id)
    {
        Log::info('looking for the message by its id' . $id);
        foreach ($this->messages as $message) {
            if (in_array($id, $message)) {

                $this->message = $message;
                break;
            }
        }
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
