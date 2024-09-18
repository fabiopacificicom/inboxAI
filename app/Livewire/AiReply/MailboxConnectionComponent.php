<?php

namespace App\Livewire\AiReply;

use App\Models\Message;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class MailboxConnectionComponent extends Component
{


    // mailbox connector settings
    public $host;
    public $port;
    public $encryption;
    public $username;
    public $password;
    public $filter; // Default filter
    public $limit;
    public $fetching = false;



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
        return view('livewire.ai-reply.mailbox-connection-component');
    }

    public function mount($settings)
    {

        // mailbox
        $this->username = $settings['username'] ?? config('responder.imap.username');
        $this->password = $settings['password'] ?? config('responder.imap.password');
        $this->host = $settings['host'] ?? config('responder.imap.server');
        $this->port = $settings['port'] ?? '993';
        $this->encryption = $settings['encryption'] ?? 'ssl';
        $this->filter = $settings['filter'] ?? 'day';
        $this->limit = $settings['limit'] ?? 15;
    }

    #[On('sync-mailbox')]
    public function connectMailbox()
    {
        // deletes all messages from the cache
        Cache::forget('messages');

        // Ensure this is uncommented to validate inputs
        $this->validate();

        // set the loading indicator
        $this->fetching = true;

        try {
            $mailbox = $this->establishMailboxConnection();
            $mailsIds = $this->searchEmails($mailbox);
            $this->fetchEmailMessages($mailbox, $mailsIds, $this->limit);
        } catch (ConnectionException $ex) {
            $this->handleConnectionException($ex);
        } finally {
            if (isset($mailbox)) {
                $mailbox->disconnect();
            }
            $this->fetching = false;
        }
    }

    private function establishMailboxConnection($folder = 'INBOX')
    {


        /* @todo
         These constants seems to be missing when the app is packaged using native script
        to build a desktop app.
        If activated the webapp stops working with a constant already declared error.
       define('OP_READONLY', 0);
        define('OP_ANONYMOUS', 0);
        define('OP_HALFOPEN', 0);
        define('CL_EXPUNGE', 0);
        define('OP_DEBUG', 0);
        define('OP_SHORTCACHE', 0);
        define('OP_SILENT', 0);
        define('OP_PROTOTYPE', 0);
        define('OP_SECURE', 0);
        define('SE_UID', 0); */




        return new Mailbox(
            '{' . $this->host . ':' . $this->port . '/imap/ssl}' . $folder, // IMAP server and mailbox folder
            $this->username, // Username for the before configured mailbox
            $this->password, // Password for the before configured username
            storage_path('app'), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            true // Attachment filename mode (optional; false = random filename; true = original filename)
            // ... existing parameters ...
        );
    }

    /**
     * @param Mailbox $mailbox IMAP mailbox connection
     */
    private function searchEmails($mailbox)
    {

        //dd($mailbox);
        // Get all emails (messages)
        // PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php

        return Cache::remember(
            'mailIds',
            now()->addHour(),
            function () use ($mailbox) {
                $date = Carbon::now()->subDays($this->getDays())->format('Ymd');
                //dd($date);
                $criteria = 'SINCE "' . $date . '"';
                //dd($criteria);
                $mailsIds = $mailbox->searchMailbox($criteria);
                return $mailsIds;
            }
        );
    }

    private function fetchEmailMessages($mailbox, $mailsIds, $limit = 15)
    {
        //dd($mailbox, $mailsIds);

        if (!$mailsIds) {
            //dd('here');
            Log::info('Mailbox is empty');

            return to_route('dashboard')->with('message', 'Mailbox is empty');
        }
        // Put the latest email on top of listing
        rsort($mailsIds);
        //dd($mailsIds);
        // Get the last 15 emails only (@todo make this dynamic)
        array_splice($mailsIds, $limit);

        // Loop through emails one by one


        $messages = Cache::remember('messages', now()->addHour(), function () use ($mailsIds, $mailbox) {
            return array_map(function ($num) use ($mailbox) {
                //dd($mailbox);
                // this fetches only the header
                //$head = $mailbox->getMailHeader($num);
                //dd($head);
                $markAsSeen = true;
                $mail = $mailbox->getMail($num, $markAsSeen);
                //dd($num, $mail);

                $message = [
                    'message_identifier' => $mail->id,
                    'is_seen' => $mail->isSeen,
                    'is_answered' => $mail->isAnswered,
                    'is_recent' => $mail->isRecent,
                    'is_flagged' => $mail->isFlagged,
                    'is_deleted' => $mail->isDeleted,
                    'is_draft' => $mail->isDraft,
                    'subject' => $mail->subject,
                    'from' => $mail->fromAddress,
                    'sender' => isset($mail->fromName) ? $mail->fromName : '',
                    'reply_to_addresses' => array_keys($mail->replyTo),
                    'date' => $this->normalizeDate($mail->date),
                    'content' => str_replace(["\t", "\r", "\n"], "", $mail->textPlain)
                    // Add more fields as needed
                ];
                //dd($message);

                //dd($message['message_identifier'], $message);

                $messageObject = Message::updateOrCreate(['message_identifier' => $mail->messageId], [
                    'message_identifier' => $message['message_identifier'],
                    'subject' => $message['subject'],
                    'from' =>  $message['from'],
                    'sender' => $message['sender'],
                    'reply_to_addresses'  => $message['reply_to_addresses'],
                    'date' => $message['date'],
                    'content' => $message['content'],
                    'is_seen' => $message['is_seen'],
                    'is_answered' => $message['is_answered'],
                    'is_recent' => $message['is_recent'],
                    'is_flagged' => $message['is_flagged'],
                    'is_deleted' => $message['is_deleted'],
                    'is_draft' => $message['is_draft']

                ]);
                //dd($messageObject);


                return $messageObject;
            }, $mailsIds);
            //dd($messages);
        });



        //dd($messages);


        $this->dispatch('mailbox-sync-event')->to(MessageListComponent::class);
    }

    private function handleConnectionException($ex)
    {
        $message = "IMAP connection failed: " . $ex->getMessage();
        $this->addError('connection', $message);
        Log::error($message);
    }


    public function updated($name, $value)
    {

        Setting::updateOrCreate(['key' => $name], ['value' => $value]);


        if ($name === 'filter') {
            $this->fetching = true;
            $this->connectMailbox();
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

    private function normalizeDate($dateString)
    {
        //dd($dateString);
        // Define an array of known formats
        $knownFormats = [
            'Y-m-d\TH:i:sP',    // Example: 2024-08-06T13:29:12+00:00
            // Add more formats as needed
            'l, d F Y H:i:s P' // Example: Tue, 6 Aug 2024 19:51:35 +0800
        ];

        // Attempt to parse the date using known formats
        foreach ($knownFormats as $format) {

            try {
                //code...
                $date = Carbon::createFromFormat($format, $dateString);
            } catch (\Throwable $th) {
                dd($th->getMessage(), $format, $dateString);
            }

            if ($date !== false) {
                return $date;
            }
        }

        // As a last resort, try letting Carbon parse the date automatically
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            // Handle the exception if the date cannot be parsed
            // This could log an error, return null, or use a default date
            Log::error('❌MailBoxConnectionComponent Error:' . $e->getMessage(), ['date' => $dateString]);
            return null;
        }
    }
}
