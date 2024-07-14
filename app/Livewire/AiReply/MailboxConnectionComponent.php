<?php

namespace App\Livewire\AiReply;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MailboxConnectionComponent extends Component
{


    // mailbox connector settings
    public $host = 'mail.fabiopacifici.com';
    public $port = '993';
    public $encryption = 'ssl';
    public $username;
    public $password;
    public $filter = 'day'; // Default filter
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
        $this->username = config('responder.imap.username');
        $this->password = config('responder.imap.password');
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
        $this->validate();
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
