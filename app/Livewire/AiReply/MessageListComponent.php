<?php

namespace App\Livewire\AiReply;

use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Traits\Calendarable;
use App\Traits\Processable;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class MessageListComponent extends Component
{

    use WithPagination, Calendarable, Processable;


    public $messages;
    public $settings;
    public $limit;
    public $period;

    /**
     * Livewire: mouth method
     * @params $settings - array of settings
     */
    public function mount($settings)
    {

        // get the mailboxes to show
        $mailbox = $this->makeMailboxFrom();
        $this->mailboxes = Cache::rememberForever('mailboxes', function () use ($mailbox) {
            return $mailbox->getMailboxes();
        });

        // TODO: use or remove this method
        // Remove old messages
        $this->removeOlderMessages();

        // update the settings
        //dd($settings);
        $this->settings = $settings;
        $this->period = $settings['filter'] ?? 'day';
        $this->limit = $settings['limit'] ?? 15;

        // retrieve the messages
        //dd(Cache::get('messages'));
        $this->messages = Cache::get('messages') ?? $this->retreiveLatestMessages();
        //dd('here');
        Log::info('MessageListComponent Mounted', [$this->messages]);
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
    public function updated($name, $value)
    {




        if ($name === 'filter' || $name == 'limit') {
            Setting::updateOrCreate(['key' => $name], ['value' => $value]);
            $this->dispatch('sync-mailbox')->to(MailboxConnectionComponent::class);
        }
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
        // sets the processing messages to an empty array
        $this->processingMessages = [];

        // 1. set the message for processing
        $message = $this->setMessage($messageId);
        //dd($message);

        // 2. classify the message for further processing
        $response = $this->classify($message);
        //dd($response);

        // 3. Extract the data from the classifier response
        [$category, $action, $instructions] = $this->extractDataFrom($response);
        //dd($category, $action, $instructions);

        // 4. Perform the actions on the message based on the action
        $this->performActions($action, $instructions, $messageId, $category, $settings = $this->settings);
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
        $mailbox = $this->makeMailboxFrom();
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

    #[On('mailbox-sync-event')]
    /**
     * Update Messages when the event is triggered.
     * @param $data
     * @return void
     */
    public function updateMessages()
    {
        //dd($data);
        $this->messages = Cache::get('messages');
        //dd($this->messages);
    }




    /**
     * Retreive the latest messages from the db
     */
    private function retreiveLatestMessages()
    {
        $limit = $this->settings['limit'] ?? 20;
        $messages = Message::orderByDesc('date')->take($limit)->get();
        //dd($messages);
        // Update the cache with the retrieved and limited messages
        Cache::forever('messages', $messages);

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
        Message::where('date', '<=', $timestamp)->take($this->limit)->delete();
        // Clean the cached messages
        Cache::forget('messages');

    }
}
