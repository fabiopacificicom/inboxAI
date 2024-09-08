<?php

namespace App\Livewire\AiReply;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Traits\Calendarable;
use App\Traits\Processable;

class MessageListComponent extends Component
{

    use WithPagination, Calendarable, Processable;


    public function mount()
    {
        $this->messages = Cache::get('messages', []);
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
