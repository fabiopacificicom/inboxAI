<?php

namespace App\Livewire\AiReply;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class MessageListComponent extends Component
{

    public $reply = [];
    public $message;
    public $messages;
    public $fetching = false;
    public $selectedModel;
    public $assistantSystem;
    public $ollamaServerAddress;
    public function mount($messages, $selectedModel, $assistantSystem, $ollamaServerAddress)
    {
        $this->selectedModel = $selectedModel;
        $this->assistantSystem = $assistantSystem;
        $this->ollamaServerAddress = $ollamaServerAddress;

        $this->messages = $messages;
    }
    public function render()
    {
        return view('livewire.ai-reply.message-list-component');
    }

    #[On('mailbox-sync-event')]
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
    public function generateReplyFor($messageId): void
    {
        //dd($messageId);
        $this->setMessage($messageId);

        //dd($this->message);
        // prepare the payload
        $payload = [
            'model' => $this->selectedModel,
            'stream' => false,
            'messages' => [

                [
                    'role' => 'system',
                    'content' => $this->assistantSystem
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->message)
                ]
            ]
        ];

        //dd($payload);
        $response = Http::post($this->ollamaServerAddress, $payload);


        $response->onError(function ($message) {
            Log::error('❌ Error: ' . $message);
            exit(1);
        });

        //dd($response->json());
        Log::info('This is the ollama response', ['response' => $response->json()]);
        // the reply message array
        $this->reply[$messageId] = $response->json(); // Get the response


    }


    public function setMessage($id)
    {
        Log::info('looking for the message by its id' . $id);
        foreach ($this->messages as $message) {
            if (in_array($id, $message)) {

                $this->message = $message;
                break;
            }
        }
    }
}
