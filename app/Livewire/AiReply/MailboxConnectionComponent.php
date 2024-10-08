<?php

namespace App\Livewire\AiReply;

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

    public function mount()
    {
        // mailbox
        $this->username = Setting::where('key', 'username')->first()?->value ?? config('responder.imap.username');
        $this->password = Setting::where('key', 'password')->first()?->value ?? config('responder.imap.password');
        $this->host = Setting::where('key', 'host')->first()?->value ?? config('responder.imap.server');
        $this->port = Setting::where('key', 'port')->first()?->value ?? '993';
        $this->encryption = Setting::where('key', 'encryption')->first()?->value ?? 'ssl';
        $this->filter = Setting::where('key', 'filter')->first()?->value ?? 'day';
        $this->limit = Setting::where('key', 'limit')->first()?->value ?? 15;
    }

    #[On('sync-mailbox')]
    public function connectMailbox()
    {
        Cache::forget('messages');
        $this->validate(); // Ensure this is uncommented to validate inputs
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

    private function establishMailboxConnection()
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
            '{' . $this->host . ':' . $this->port . '/imap/ssl}INBOX', // IMAP server and mailbox folder
            $this->username, // Username for the before configured mailbox
            $this->password, // Password for the before configured username
            storage_path('app'), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            true // Attachment filename mode (optional; false = random filename; true = original filename)
            // ... existing parameters ...
        );
    }

    private function searchEmails($mailbox)
    {
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

        if (!$mailsIds) {
            //dd('here');
            Log::info('Mailbox is empty');

            return to_route('dashboard')->with('message', 'Mailbox is empty');
        }
        // Put the latest email on top of listing
        rsort($mailsIds);

        // Get the last 15 emails only (@todo make this dynamic)
        array_splice($mailsIds, $limit);

        // Loop through emails one by one


        $messages = Cache::remember('messages', now()->addHour(), function () use ($mailsIds, $mailbox) {
            return array_map(function ($num) use ($mailbox) {

                //dd($mailbox);
                $head = $mailbox->getMailHeader($num);
                //dd($head);
                $markAsSeen = false;
                $mail = $mailbox->getMail($num, $markAsSeen);
                //dd($mail->textPlain);
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
                    'content' => $mail->textPlain
                    // Add more fields as needed
                ];
                //dd($message);
                return $message;
            }, $mailsIds);
        });



        //dd($messages);


        $this->dispatch('mailbox-sync-event', $messages)->to(MessageListComponent::class);
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
}
