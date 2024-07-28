<?php

namespace App\Livewire\AiReply;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use PDO;

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
    public function processMessage($messageId): void
    {

        // set the message for processing
        $message = $this->setMessage($messageId);
        //dd($message);

        // classify the message for further processing
        $response = $this->classify($message);


        if ($response) {
            $decoded = json_decode($response['message']['content'], true);
            //dd($decoded);
            $category = $decoded['category'];
            //TODO:
            $this->moveMessage($this->message, $category);


            $action = $decoded['action'];

            // move the message to the assigned category
            //$this->moveMessage($messageId, $category);

            // if action execute the given instructions
            if ($action) {
                // take the instructions
                $instructions = $decoded['instructions'];
                //dd($instructions);

                switch ($instructions) {
                    case 'summarize':
                    case 'generateReply':
                        $this->generateReply($messageId);
                        break;

                    case 'event':
                        $this->updateCalendar();
                        break;
                }
            }
            session()->flash('reply-generated', 'Reply generation not required');
        } else {
            session()->flash('reply-generated', 'Error, try again.');
        }

        //dd($this->reply[$messageId]);

    }


    public function moveMessage($message, $category)
    {
        //dd($message, $category);
        // TODO:
        Log::info('Move the message', ['message'=> $message, 'category' => $category]);
    }

    public function generateReply($messageId)
    {
        //dd('reply to the message', $this->message);
        // prepare the payload to process the selected message
        $payload = $this->getPayload();
        //dd($payload);
        // Use the payload to generate a response
        $this->reply[$messageId] = $this->getResponse($payload); // Get the response
        // inform the user that the generation was completed
        session()->flash('reply-generated', 'Reply Generated successfully');
        # code...


    }


    public function updateCalendar()
    {
        dd('add calendar entry', $this->message);
    }

    public function classify($message)
    {
        // set the classifier payload
        $payload = [
            'model' => Setting::where('key', 'selectedModel')->first()?->value ?? config('responder.assistant.model'),
            'stream' => false,
            'format' => 'json',
            'messages' => [

                [
                    'role' => 'system',
                    'content' => Setting::where('key', 'classifierSystem')->first()?->value ?? config('responder.classifier.system')
                ],
                [
                    'role' => 'user',
                    'content' => 'Classify the following message resource: ' . json_encode($message)
                ]
            ]
        ];
        // handle the response

        try {

            $resp = $this->getResponse($payload);
            $content = json_decode($resp['message']['content'], true);
            if (!$content || !array_key_exists('category', $content) && !array_key_exists('action', $content) && !array_key_exists('instructions', $content)) {
                session()->flash('message', 'Sorry, i had problems classifing this message, try again later.');
                Log::error('The AI model generated an incorrect response', $resp);
                return false;
            }
            // TODO: use the category to move the imap message in a dedicated folder?
            return $resp;
        } catch (\Throwable $th) {
            session()->flash('message', $th->getMessage());
            Log::error($th->getMessage());
        }
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
            throw new \Exception($message, 1);
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
            'format' => 'json',
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
     * This method searches the given message in the currently downloaded
     * messages array and sets the message property to the corresponding resource
     *
     * The message array has the structure mapped as it appears in
     * the MailboxConnectionComponent's fetchEmailMessages() method
     *
     * @return array the mailbox mapped message resource as an array
     */
    public function setMessage($id)
    {
        Log::info('looking for the message by its id' . $id);
        foreach ($this->messages as $message) {
            //dd($message);
            if ($id === $message['messageId']) {
                $this->message = $message;
                break;
            }
        }
        return $this->message;
    }
}
