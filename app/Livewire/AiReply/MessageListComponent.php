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

    /**
     * @params $settings - array of settings
     */
    public function mount($settings)
    {

        $mailbox = $this->makeMailboxFrom();
        $this->mailboxes = Cache::rememberForever('mailboxes', function () use ($mailbox) {
            return $mailbox->getMailboxes();
        });

        ///$this->removeOlderMessages();

        $this->settings = $settings;
        //dd(Cache::get('messages'));
        $this->messages = Cache::get('messages') ?? $this->retreiveLatestMessages();
        Log::info('MessageListComponent Mounted', [$this->messages]);
    }

    public function render()
    {
        return view('livewire.ai-reply.message-list-component');
    }

    public function switchMailboxFolder($mailboxFolder){
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


    /* TODO: review the method logic, you need to trash or delete
     * only messages from the currently selected mailbox.
     * and not all messages downloaded in the db for the current session
     */
    public function deleteMessages($forever = false)
    {

        // Get all message_identifiers
        $ids = Message::pluck('message_identifier');
        //dd($ids);

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
     * generates reply for a givem message
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
        $period = Setting::where('key', 'filter')->first()->value;
        //dd($period);


        // Define a mapping of intervals to their corresponding number of days
        $intervalMap = [
            'day' => 1,
            'week' => 7,
            'month' => 30, // assuming 30 days in a month for simplicity
        ];

        // Calculate the timestamp for the period (e.g. today minus the specified interval)
        $timestamp = now()->subDays($intervalMap[$period]);
        //dd($timestamp, $limit, $period, $intervalMap[$period]);

        // Delete any messages that are older than the calculated timestamp
        Message::where('date', '<=', $timestamp)->delete();
        // Clean the cached messages
        Cache::forget('messages');
        //dd(Message::all());
    }
}
