<?php

namespace App\Livewire\AiReply;

use App\Livewire\MessageCardDialog;
use App\Models\Message;
use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Calendarable;
use App\Traits\Processable;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use App\Traits\Helpers;
use App\Models\Reply;

class MessageListComponent extends Component
{

    use Calendarable, Processable, Helpers;


    public $messages;
    public $settings;
    public $limit;
    public $period;
    public $accounts = [];
    public $selectedAccountId = null; // null = All Inboxes

    /**
     * Livewire: mouth method
     * @params $settings - array of settings
     */
    public function mount($settings)
    {

        // get the mailboxes to show
        $mailbox = $this->makeMailboxFromSettings();
        $this->mailboxes = Cache::rememberForever('mailboxes', function () use ($mailbox) {
            return $mailbox->getMailboxes();
        });
        //dd($this->mailboxes);
        // Remove old messages
        //$this->removeOlderMessages();
        // update the settings
        //dd($settings);
        $this->settings = $settings;
        $this->period = $settings['filter'] ?? 'day';
        $this->limit = $settings['limit'] ?? 15;

        // Load all accounts
        $this->loadAccounts();

        // retrieve the messages
        //dd(Cache::get('messages'));
        $this->messages = $this->retreiveLatestMessages();
        //dd('here');
        //Log::info('MessageListComponent Mounted with ' . $this->messages->count() . ' messages.');
    }

    /**
     * Livewire: render method
     */
    public function render()
    {
        return view('livewire.ai-reply.message-list-component');
    }

    /**
     * Livewire: Updated hook
     *
     * @param $name - the name of the property being updated
     * @param $value - the new value for that property
     * @return void
     */
    public function updated($name, $value): void
    {

        if ($name === 'filter' || $name == 'limit') {
            Setting::updateOrCreate(['key' => $name], ['value' => $value]);
            $this->dispatch('sync-mailbox')->to(MailboxConnectionComponent::class);
        }
    }

    /**
     * Load all accounts for the account selector
     */
    private function loadAccounts()
    {
        $this->accounts = Account::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Switch to a different account or show all inboxes
     * @param int|null $accountId - null for All Inboxes, or specific account ID
     */
    public function switchAccount($accountId = null)
    {
        $this->selectedAccountId = $accountId;
        $this->messages = $this->retreiveLatestMessages();
    }


    /* public function setReplyContent($content)
    {
        dd($content);
        $this->dispatch('set-content', addslashes($content))->to(ReplyFormComponent::class);
    } */


    public function loadMore()
    {
        $this->limit += 10;
        Setting::updateOrCreate(['key' => 'limit'], ['value' => $this->limit]);
        $this->dispatch('sync-mailbox')->to(MailboxConnectionComponent::class);
    }


    public function refreshMessages()
    {
        Cache::purge('messages');
        $this->messages = $this->retreiveLatestMessages();
        $this->dispatch('sync-mailbox');
    }

    /**
     * Fetch a message from the imap server and update the corresponding model
     * in the db
     * @param $id - the id of the message to fetch
     * @return void
     */
    public function fetchMessage($id): void
    {

        $this->dispatch('fetch-message', $id)->to(MessageCardDialog::class);
        Log::info('Fetching message: ' . $id . '... event dispatched to the MessageCardDialog component');

        /*   //dd($id);
        //$this->loading = true;
        // get the message from the imap server
        $imapMailbox = $this->makeMailboxFromSettings(inbox: $this->selectedMailbox);

        $mail = $imapMailbox->getMail($id);
        //dd($mail);
        $content = $mail?->textPlain;

        if (strlen($content)  == 0) {
            $content = $mail?->textHtml;
        }
        //dd($content);
        $content =  $this->convertHtmlToPlainText($content);
        //dd($content);
        $this->fetching = false;

        // find the mesage from the db
        $message = Message::where('message_identifier', $id)->first();
        //update it
        $message->update(['content' => ($content)]);

        // update the messages collection
        $this->messages = Cache::get('messages', $this->retreiveLatestMessages());*/
    }


    /**
     * InboxAI: Process the given message with AI
     *
     * This method is responsible of performing classification
     * and other actions based on the result.
     *
     * @param string $messageId the id of the message retrieved from the imap server
     * @return void
     */
    public function processMessage($messageId): void
    {
        $this->fetchMessage($messageId);

        // sets the processing messages to an empty array
        $this->processingMessages = [];

        // 1. set the message for processing
        $message = $this->setMessage($messageId);
        //dd($message);

        // 2. classify the message for further processing
        [$action, $instructions] = $this->classify($message);
        //dd($action, $instructions);

        /* TODO:
        //Remove @deprecated
        // 3. Extract the data from the classifier response
        //[$category, $action, $instructions] = $this->extractDataFrom($response);
        //dd($category, $action, $instructions); */

        // 4. Perform the actions on the message based on the action
        $reply = $this->performActions($action, $instructions, $messageId, $this->settings);
        //$this->dispatch('set-reply', ['messageId' => $messageId, 'reply' => $this->reply[$messageId]])->to(ReplyFormComponent::class);
        //dd($reply, $messageId);
        //dd($this->message, $instructions);
        $message_content = 'Subject:' . $this->message['subject'] . '. Body: ' . $this->message['content'];
        //dd($this->message, $message_content);
        //dd(json_decode($reply, true));
        $data =  [
            'message_identifier' => $messageId,
            'message_content' => $message_content,
            'response_content' => trim(json_decode($reply, true)['reply'])
        ];

        //dd($data);
        $this->message->replies()->create($data);
        $this->dispatch('set-reply', $reply)->to(ReplyFormComponent::class);
        Log::info("reply created for message $messageId");
    }



    /**
     * InboxAI: Switch the mailbox folder
     *
     * This method is responsible of switching between
     * different folders on the imap server and sync
     * the messages from the selected folder.
     *
     * @param $mailboxFolder - the selected mailbox folder
     * @return void
     */
    public function switchMailboxFolder($mailboxFolder)
    {
        //dd($mailboxFolder);

        $this->selectedMailbox = $mailboxFolder['shortpath'];
        $mailbox = $this->makeMailboxFromSettings();
        $mailbox->switchMailbox($mailboxFolder['fullpath']);
        //dd($mailbox);
        $ids = $mailbox->searchMailbox('ALL');
        //dd($mailbox, $ids);
        $this->fetchEmailMessages($mailbox, $ids);
        $this->messages = Cache::get('messages');
        //dd($mailbox, $this->messages);
    }


    /**
     * InboxAI: Delete all the selected messages
     *
     * This method is responsible of deleting all the selected
     * from the database and trashing them from the imap server
     * or purge them permanently frmom the imap server.
     *
     * @param $forever - boolean, true to trash or delete the messages forever
     */
    public function deleteMessages($forever = false)
    {

        //dd(Message::first());
        // Get all message_identifiers where the mailbox_folder match $this->selectedMailbox
        $ids = Message::select('message_identifier')->where('mailbox_folder', $this->selectedMailbox)->pluck('message_identifier');
        //dd($ids, $this->selectedMailbox);

        // delete all messages with the ids matching from the database
        Message::whereIn('message_identifier', $ids)->delete();

        // clear the messages from the imap server
        $forever ? $this->deleteImapMessagesByMessageIds($ids) : $this->trashImapMessagesByMessageIds($ids);
        Cache::flush();

        // empty the messages array
        $this->messages = [];

        session()->flash('message', 'All Messages Deleted!');

        return redirect()->back()->with('message', 'All Messages Deleted!');
    }

    /**
     * InboxAI: Clean the inbox Trash folder
     * by removing all messages permanently
     * from the imap server's trash folder
     */
    public function cleanTrash()
    {
        //dd('cleaning the trash mailbox folder');
        $this->emptyMailbox('IMAP.Trash');
        //dd('cleaned');
        Cache::flush();
    }

    /**
     * Update Messages when the event is triggered.
     * @param $data
     * @return void
     */
    #[On('mailbox-sync-event')]
    public function updateMessages()
    {
        //dd($data);
        $this->messages = Cache::get('messages');
        //dd($this->messages);
    }


    /**
     * Retreive the latest messages from the db
     * Filters by selected account if specified, otherwise shows all active accounts
     */
    private function retreiveLatestMessages()
    {
        $limit = $this->settings['limit'] ?? 20;

        $query = Message::with(['replies', 'account']);

        // Filter by selected account or all active accounts
        if ($this->selectedAccountId) {
            // Show messages from specific account
            $query->where('account_id', $this->selectedAccountId);
        } else {
            // Show messages from all active accounts (unified inbox)
            $query->whereHas('account', function($q) {
                $q->where('is_active', true);
            });
        }

        $messages = $query->orderByDesc('date')->take($limit)->get();
        //dd($messages);

        return $messages;
    }

    /**
     * Remove old messages from the cache and the db
     */
    private function removeOlderMessages()
    {
        // get the period of messages to be displayed from the settings table
        $this->period = Setting::where('key', 'filter')->first()->value;


        // Define a mapping of intervals to their corresponding number of days
        $intervalMap = [
            'day' => 1,
            'week' => 7,
            'month' => 30, // assuming 30 days in a month for simplicity
        ];

        // Calculate the timestamp for the period (e.g. today minus the specified interval)
        $timestamp = now()->subDays($intervalMap[$this->period]);
        //dd($timestamp);

        // Delete any messages that are older than the calculated timestamp
        Message::with('replies')->where('date', '<=', $timestamp)->take($this->limit)->forceDelete();
        // Clean the cached messages
        Cache::forget('messages');
    }
}
