<?php

namespace App\Livewire\AiReply;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
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


    public function mount()
    {
        $this->messages = Cache::get('messages', []);
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
        $payload = $this->getPayload();

        //dd($payload);
        //dd($payload, $this->ollamaServerAddress, $this->assistantSystem);
        // the reply message array
        $this->reply[$messageId] = $this->getResponse($payload); // Get the response
        //dd($this->reply)

        session()->flash('reply-generated', 'Reply Generated successfully');


    }

    /**
     * Get the ollama response for the given payload
     * @returns array the http response as an array
     */
    private function getResponse($payload): array
    {
        Log::info("This is the payload:", $payload);
        $response = Http::timeout(5000)->post(Setting::where('key', 'ollamaServerAddress')->first()?->value ?? config('responder.assistant.server'), $payload);

        $response->onError(function ($message) {
            Log::error('❌ Error: ' . $message);
            exit(1);
        });

        //dd($response->json());
        Log::info('This is the ollama response', ['response' => $response->json()]);
        return $response->json();
    }

    /**
     * get the payload for the ollama api request
     *
     * @returns array
     */
    private function getPayload(): array
    {
        return [
            'model' => Setting::where('key', 'selectedModel')->first()?->value ?? config('responder.assistant.model'),
            'stream' => false,
            'messages' => [

                [
                    'role' => 'system',
                    'content' => Setting::where('key', 'assistantSystem')->first()?->value ?? config('responder.assistant.system')
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->message)
                ]
            ]
        ];
    }
    /**
     * Sets the message for the given message id
     * This method searches the given message id and sets it
     */
    public function setMessage($id): void
    {
        Log::info('looking for the message by its id' . $id);
        foreach ($this->messages as $message) {
            //dd($message);
            if ($id === $message['messageId']) {
                $this->message = $message;
                break;
            }
        }
    }
}
