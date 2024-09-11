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


    public function mount()
    {
        // get the limit of messages to be displayed from the settings table
        $limit = Setting::where('key', 'limit')->first()->value;
        $period = Setting::where('key', 'filter')->first()->value;
        //dd($limit, $period);


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

        // Retrieve the latest N messages from the database, where N is the maximum allowed limit
        $messages = Message::orderByDesc('created_at')->take($limit)->get();
        // Update the cache with the retrieved and limited messages
        Cache::forever('messages', $messages);


        $this->messages = Cache::get('messages', $messages);
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
    public function updateMessages($data)
    {
        //dd($data);
        $this->messages = $data;
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
        $this->performActions($action, $instructions, $messageId, $category);
    }
}
