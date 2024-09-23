<?php

namespace App\Livewire\AiReply;

use PhpImap\Exceptions\ConnectionException;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Traits\HasMailboxConnection;

class MailboxConnectionComponent extends Component
{


    use HasMailboxConnection;
    // mailbox connector settings
    public $host;
    public $port;
    public $encryption;
    public $username;
    public $password;
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
    /*
     * TODO: review this method, consider moving the logic in the trait for reusability
     * Note: There is a method that does a similar thing see if can use it instead and merge the necessary logic
     */
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

    public function updated($name, $value)
    {

        Setting::updateOrCreate(['key' => $name], ['value' => $value]);

    }
}
