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

class MessageListComponent extends Component
{

    use WithPagination, Calendarable, Processable;


    public $settings;
    public function mount($settings)
    {

        // clean up older messages from the cache and db
        //$this->removeOlderMessages();
        // Retrieve the latest N messages from the database, where N is the maximum allowed limit
        $this->settings = $settings;
        //dd(Cache::get('messages'));
        $this->messages = Cache::get('messages', $this->retreiveLatestMessages());
    }


    private function retreiveLatestMessages(){
        $limit = $this->settings['limit'] ?? 20;
        $messages = Message::orderByDesc('date')->take($limit)->get();
        //dd($messages);
        // Update the cache with the retrieved and limited messages
        Cache::forever('messages', $messages);

        return $messages;

    }

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



    public function render()
    {
        return view('livewire.ai-reply.message-list-component');
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
}
