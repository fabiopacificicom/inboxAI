<?php

namespace App\Livewire\AiReply;

use App\Models\Setting;
use Google_Service;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;

class MessageListComponent extends Component
{

    use WithPagination;
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
        $this->message =   [...array_filter($this->messages, fn($message) => $id === $message['messageId'])][0];
        //dd($this->message);
        Log::info('1️⃣SetMessage -> Message ID: ' . $id, ['message' => $this->message]);

        //dd($this->message);
        /* foreach ($this->messages as $message) {
            //dd($message);
            if ($id === $message['messageId']) {
                $this->message = $message;
                Log::info('Message: ', $message);
                break;
            }
        } */
        return $this->message;
    }

    /**
     * Classify the given message
     * This method uses the selected classifier to determine which category
     * the message belongs to.
     *
     * @param array $message the message resource as an array
     * @return array the message classified as an array
     */
    public function classify($message)
    {
        //dd($message);
        // set the classifier payload
        $payload = [
            'model' => Setting::where('key', 'selectedClassifier')->first()?->value ?? config('responder.classifier.model'),
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
        Log::info('2️⃣Classification Payload: ', ['payload' => $payload]);
        try {

            $resp = $this->getResponse($payload);
        } catch (\Throwable $th) {
            session()->flash('message', $th->getMessage());
            Log::error($th->getMessage());
        }

        //dd($resp);
        $content = json_decode($resp['message']['content'], true);
        if (!$content || !array_key_exists('category', $content) && !array_key_exists('action', $content) && !array_key_exists('instructions', $content)) {
            session()->flash('message', 'Sorry, i had problems classifing this message, try again later.');
            Log::error('❌CLASSIFICATION - The AI model generated an incorrect response, see the response below.', $resp);

            return false;
        }
        Log::info('✅CLASSIFICATION COMPLETE.', ['classification_response' => $resp]);
        return $resp;
    }


    /**
     * Extract the data from the provided response
     * @param $response
     * @return array The data extracted from the response [category, action, instructions]
     */
    public function extractDataFrom($response)
    {
        Log::info(' 3️⃣Extract data from the response');
        if (!$response) {
            session()->flash('reply-generated', 'Error, try again.');
            return;
        }

        $decoded = json_decode($response['message']['content'], true);
        //dd($decoded);
        $category = $decoded['category'];
        $action = $decoded['action'];
        $instructions = $decoded['instructions'];
        Log::info('✅Extraction completed', ['data' => ['category' => $category, 'action' => $action, 'instructions' => $instructions]]);
        return [$category, $action, $instructions];
    }

    public function performActions($action, $instructions, $messageId, $category = null)
    {
        if (!$action) {
            session()->flash('reply-generated', 'No action required.');
            return;
        }
        Log::info('4️⃣ performActions', ['instructions' => $instructions, 'action' => $action, 'category' => $category, $messageId => $this->message]);

        /* TODO:
        - 1. categorize the message. `$this->categorizeMessage($messageId, $category);`
        - 2. generate a reply `$this->generateReply($messageId, $instructions)`;
        - 3. add a calendar entry if required `if ($instructions['insertEvent']) $this->updateCalendar($reply)`;
         */

        // $this->categorizeMessage($messageId, $category);

        // Generate a reply for the given message

        $this->generateReply($messageId, $instructions);

        // Add a calendar entry if required
        //dd($this->reply[$messageId]);

        if ($instructions == 'insertEvent' ||  array_key_exists('event', json_decode($this->reply[$messageId]['message']['content'], true)) && json_decode($this->reply[$messageId]['message']['content'], true)['event']) {

            /* TODO:
        before we actually schedule an appointment for the given reply we shoud check if we are actually free for that dateTime
        - get the events for the requested date and time
        - check if the user is free for that dateTime
        - if the user is free for that dateTime then schedule the appointment
        - else return an error message that the user is not free for that dateTime
        - update the reply accordingly to request a new datetime for proposed appointment
        */
            // get the requested datees fro mteh reply
            $startDateTime = Carbon::parse(json_decode($this->reply[$messageId]['message']['content'], true)['event']['start']['dateTime']);
            $endDateTime = Carbon::parse(json_decode($this->reply[$messageId]['message']['content'], true)['event']['end']['dateTime']);

            // check calendar availability
            $is_available = $this->checkCalendarAvailability($startDateTime, $endDateTime);

            //dd($is_available);
            // schedule the appointment if available or propose a different date/time
            if ($is_available) {
                $this->updateCalendar($messageId, $this->reply[$messageId]);
            }
        }
    }



    private function generateReply($messageId, $instructions)
    {
        Log::info('5️⃣ Generate a reply...');
        //dd($messageId, $instructions);
        //dd('reply to the message', $this->message);
        // prepare the payload to process the selected message
        $payload = $this->getPayload($instructions);
        //dd($payload);
        // Use the payload to generate a response
        $this->reply[$messageId] = $this->getResponse($payload); // Get the response
        // inform the user that the generation was completed
        session()->flash('reply-generated', 'Reply Generated successfully');
        Log::info('✅Reply generated', ['reply' => $this->reply[$messageId]]);

        //$this->dispatch('reply-generated', $this->reply[$messageId]);


    }



    /**
     * get the payload for the ollama api request
     *
     * @returns array
     */
    private function getPayload($instructions = null): array
    {

        if (is_array($instructions)) $instructions = join(',', $instructions);
        // if ($instructions) $message = ['role'=> 'user', 'content'=> "Instructions: $instructions"];
        //dd($instructions, $this->message);
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
                    'content' => json_encode($this->message) . ' Action to take: ' . $instructions
                ]
            ]
        ];
    }

    /**
     * Get the ollama response for the given payload
     * @returns array the http response as an array
     */
    private function getResponse($payload): array
    {

        $response = Http::timeout(5000)
            ->post(Setting::where('key', 'ollamaServerAddress')
                ->first()?->value ?? config('responder.assistant.server'), $payload);

        $response->onError(function ($message) {
            Log::error('❌ Error: ' . $message);
            throw new \Exception($message, 1);
        });

        //dd($response->json());
        return $response->json();
    }




    private function checkCalendarAvailability($startDateTime, $endDateTime){


        /* Return true if available / false otherwise */

        $events = Event::get($startDateTime, $endDateTime);
        if ($events->count() === 0){
            return true;
        }
        return false;
        //dd($events->count() === 0, $startDateTime, $endDateTime);
    }


    #[On('reply-generated')]
    /**
     * TODO:
     * Update the calendar
     * @param array $reply
     * @return void
     */
    public function updateCalendar($messageId, $reply)
    {


        Log::info('6️⃣ Updating calendar for the previous reply');
        /* TODO:
        Handle the reply here, inside the reply thereis the google calendar event json
        to use with the spatie package to insert calendar events. */
        //dd($messageId, $reply);
        $replyContent = json_decode($reply['message']['content'], true);


        if (!$replyContent['event']) {
            throw new \Exception("Missing event key in the provided response", 1);
        }
        Log::info('👉 Reply content', ['reply' => $replyContent]);
        //dd($replyContent['event'], $replyContent['reply']);

        //https://packagist.org/packages/spatie/laravel-google-calendar

        $event = new Event();
        $event->name = $replyContent['event']['summary'];
        $event->description = $replyContent['event']['description'] ?? '';
        $event->startDateTime = Carbon::parse($replyContent['event']['start']['dateTime']);
        $event->endDateTime = Carbon::parse($replyContent['event']['end']['dateTime']);
        //$event->addAttendee($replyContent['event']['attendees'] ?? []);
        $event->save();
        Log::info('✅Event created', ['event' => $event]);
        //dd($event);
        session()->flash('message', $replyContent['reply'] . 'event ' . $replyContent['event']['summary'] . 'was created');

        return redirect()->back();
    }

    /**
     * TODO:
     * Move a message to a category on the IMAP server
     * @param $message the message to be moved
     * @param $category the category to move it into
     * @return void
     */
    public function categorizeMessage($message, $category)
    {
        dd('TODO: Move the message in a dedicated folder on the IMAP server', $message, $category);
        // TODO:
        Log::info('Move the message', ['message' => $message, 'category' => $category]);
    }


}
