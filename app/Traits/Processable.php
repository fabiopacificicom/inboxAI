<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Traits\HandleAiResponse;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use PhpImap\Mailbox;
use Illuminate\Support\Str;
use PhpImap\Imap;

trait Processable
{
    use HandleAiResponse;
    public $message;
    public $messages;
    public $reply = [];
    public $fetching = false;
    public $processingMessages = [];


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
    private function setMessage($id)
    {
        //dd($this->messages);

        // if is an array
        if (is_array($this->messages)) {
            $this->message =   [...array_filter($this->messages, fn($message) => $id === $message['message_identifier'])][0];
        } else {
            // is a collection
            $this->message = $this->messages->filter(fn($message) => $id === $message['message_identifier'])->first();
        }



        //dd($this->message);
        Log::info('1️⃣SetMessage -> Message ID: ' . $id, ['message' => $this->message]);
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
    private function classify($message)
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
            $this->processingMessages[] = ['❌' => 'Classification failed, try again later.'];
            Log::error('❌CLASSIFICATION - The AI model generated an incorrect response, see the response below.', $resp);

            return false;
        }
        $this->processingMessages[] = ['✅' => 'Message classified successfully'];
        Log::info('✅CLASSIFICATION COMPLETE.', ['classification_response' => $resp]);
        return $resp;
    }

    /**
     * Extract the data from the provided response
     * @param $response
     * @return array The data extracted from the response [category, action, instructions]
     */
    private function extractDataFrom($response)
    {
        Log::info(' 3️⃣Extract data from the response');
        $this->processingMessages[] = ['✅' => 'Extracting data from the response'];
        if (!$response) {
            session()->flash('reply-generated', 'Error, try again.');
            return;
        }

        $decoded = json_decode($response['message']['content'], true);
        //dd($decoded);
        $category = $decoded['category'];
        $action = $decoded['action'];
        $instructions = $decoded['instructions'] ?? '';
        $this->processingMessages[] = ['✅' => 'Data extracted successfully'];
        Log::info('✅Extraction completed', ['data' => ['category' => $category, 'action' => $action, 'instructions' => $instructions]]);
        return [$category, $action, $instructions];
    }


    /**
     * Perform the actions based on the extracted data
     * @param $action
     * @param $instructions
     * @param $messageId
     * @param $category
     * @return void
     */
    private function performActions($action, $instructions, $messageId, $category = null, $settings = null)
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

        $this->categorizeMessage($messageId, $category, $settings);
        $this->processingMessages[] = ["✅" => "Message Categorized: $category"];
        // Generate a reply for the given message

        $this->generateReply($messageId, $instructions);
        $this->processingMessages[] = ["✅" => "Reply generated"];

        // Add a calendar entry if required
        Log::info('👉Reply', ['reply' => $this->reply[$messageId]]);

        /* dd(
            array_key_exists('event', json_decode($this->reply[$messageId]['message']['content'], true)),
            json_decode($this->reply[$messageId]['message']['content'], true)['event'] == true,
            ($instructions == 'insertEvent' || is_array($instructions) && in_array('insertEvent', $instructions))
        ); */

        if (
            array_key_exists('event', json_decode($this->reply[$messageId]['message']['content'], true)) &&
            json_decode($this->reply[$messageId]['message']['content'], true)['event'] == true &&
            ($instructions == 'insertEvent' || is_array($instructions) && in_array('insertEvent', $instructions))

        ) {

            // get the requested datees fro mteh reply
            $startDateTime = Carbon::parse(json_decode($this->reply[$messageId]['message']['content'], true)['event']['start']['dateTime']);
            $endDateTime = Carbon::parse(json_decode($this->reply[$messageId]['message']['content'], true)['event']['end']['dateTime']);

            // check calendar availability
            $is_available = $this->checkCalendarAvailability($startDateTime, $endDateTime);
            $this->processingMessages[] = ["✅" => "Checking calendar availability"];

            //dd($is_available);
            // schedule the appointment if available or propose a different date/time
            if ($is_available) {
                $this->updateCalendar($messageId, $this->reply[$messageId]);
            }

            $this->processingMessages[] = ["✅" => "Calendar Event added"];
        }
    }



    /**
     * TODO:
     * Move a message to a category on the IMAP server
     * @param $message the message to be moved
     * @param $category the category to move it into
     * @return void
     */
    public function categorizeMessage($id, $category, $settings)
    {
        $this->processingMessages[] = ["✅" => "Categorising message"];
        if (strtolower($category) === 'inbox') return;
        // get the mailbox settings from the parameters or use the default settings
        $username = $settings['username'] ?? config('responder.imap.username');
        $password = $settings['password'] ?? config('responder.imap.password');
        $host = $settings['host'] ?? config('responder.imap.server');
        $port = $settings['port'] ?? '993';

        // connect to the IMAP server and open a connection
        $mailbox = new Mailbox(
            '{' . $host . ':' . $port . '/imap/ssl}INBOX', // IMAP server and mailbox folder
            $username, // Username for the before configured mailbox
            $password, // Password for the before configured username
            storage_path('app'), // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            true // Attachment filename mode (optional; false = random filename; true = original filename)
        );

        // get all mailboxes from the IMAP server and filter the mailboxes by category
        $mailBoxes = $mailbox->getMailboxes();
        //dd($mailBoxes);


        $mailboxCategory = [...array_filter($mailBoxes, function ($mailCategory) use ($category) {
            return $mailCategory['shortpath'] === 'INBOX.' . ucfirst($category);
        })];

        if (empty($mailboxCategory) || count($mailboxCategory) == 0) {
            throw new \Exception("The provided category is not a vailad mailbox folder", 1);
        }

        $mailboxFolder = $mailboxCategory[0]['shortpath'];

        //dd([...$mailboxCategory]);
        $mailbox->moveMail($id, $mailboxFolder);
        $this->processingMessages[] = ["✅" => 'The message with id:' . $id . 'to category: ' . $category . 'was moved'];
        Log::info('Move the message with id:', ['id' => $id, 'category' => $category]);
    }


    /**
     * Generate a reply for the given message
     * @param $messageId
     * @param $instructions
     * @return void
     */
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
        $this->processingMessages[] = ["✅" => 'The reply was generated successfully'];
        session()->flash('reply-generated', 'Reply Generated successfully');
        Log::info('✅Reply generated', ['reply' => $this->reply[$messageId]]);

        //$this->dispatch('reply-generated', $this->reply[$messageId]);


    }



    /**
     * get the payload for the ollama api request
     *
     * @param $instructions
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
}
