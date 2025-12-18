<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Url;
use App\Traits\HandleAiResponse;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpImap\Imap;
use App\Traits\Helpers;
use App\Traits\HasMailboxConnection;
use Livewire\Attributes\Reactive;

trait Processable
{
    use HandleAiResponse, HasMailboxConnection, Helpers;
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
            $this->message =   [...array_filter($this->messages, fn($message) => $id == $message['message_identifier'])][0];
        } else {
            // is a collection
            $this->message = $this->messages->filter(fn($message) => $id == $message['message_identifier'])->first();
        }

        //dd($this->message);
        Log::info('1️⃣SetMessage -> Message ID: ' . $id, ['message' => $this->message['subject']]);
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
                    'content' => 'Sender: ' . $message['sender'] . 'Subject: ' . $message['subject'] . ' Content:' . $this->convertHtmlToPlainText($message['content']),
                ]
            ],
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'classify',
                        'description' => 'classify the given message IMAP resource',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'category' => [
                                    'type' => 'string',
                                    'description' => 'the category where place the classified messge'
                                ],
                                'action' => [
                                    'type' => 'boolean',
                                    'description' => 'true if an action is required',
                                ],
                                'instructions' => [
                                    'type' => 'array',
                                    'description' => 'the instructions to be followed',
                                ]
                            ]
                        ],
                        'required' => ['category', 'action', 'instructions']
                    ]
                ]
            ]
        ];
        //dd($payload);

        // handle the response
        // {\"category\": \"inbox\", \"action\": true, \"instructions\": [\"generateReply\", \"insertEvent\"]}
        Log::info('2️⃣Classification Payload ready', ['payload' => $payload]);
        try {

            $resp = $this->getResponse($payload);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $th->getMessage();
        }

        // 📌 NOTE: If the model does not support function calling the classification will always fail.
        // call the categorizer tools
        $classificationResponse =  $this->callTheClassificationToolIfSupported($resp, $message['id']);

        if ($classificationResponse === false) {
            Log::error('❌CLASSIFICATION - The AI model generated an incorrect response, see the response below.', $resp);
            $this->processingMessages[] = ['❌' => 'Classification failed, try again later.'];
            return [false, $resp['message']['content']];
        }
        return $classificationResponse;
    }



    /**
     * Call the categorizer tools
     * @param array $resp
     * @return array || false
     */
    private function callTheClassificationToolIfSupported($resp, $messageId)
    {

        $tools = $resp['message']['tool_calls'] ?? null;
        if ($tools && strtolower($tools[0]['function']['name']) === 'classify') {

            $action = $tools[0]['function']['arguments']['action'];
            $instructions = array_key_exists('instructions', $tools[0]['function']['arguments']) ? $tools[0]['function']['arguments']['instructions'] : '';
            $category =
                preg_replace('/[^A-Za-z0-9\-\s]/', '', strtolower($tools[0]['function']['arguments']['category'])) ?? 'unknown' . now()->year;

            $this->processingMessages[] = ['✅' => 'Message classified successfully'];
            Log::info("Category: $category");
            $this->categorizeMessage($messageId, $category);
            Log::info('✅CLASSIFICATION COMPLETE.');
            return [$action, $instructions];
        }
        return false;
    }




    /**
     * Extract the data from the provided response.
     * This is useful with models that do not support function calling
     * and could use this to extract from the provided response the necessary valiables
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
     * TODO:
     * Move a message to a category on the IMAP server
     * @param $message the message to be moved
     * @param $category the category to move it into
     * @return void
     */
    private function categorizeMessage($id, $category)
    {
        if (strtolower($category) === 'inbox') return;
        $this->processingMessages[] = ["🎯" => "Categorizing message on $category"];


        // connect the mailbox
        $mailbox = $this->makeMailboxFromSettings();

        // get all mailboxes from the IMAP server and filter the mailboxes by category
        $mailBoxes = $mailbox->getMailboxes();
        //dd($mailBoxes);

        // get the first mailbox that matches the category
        $mailboxPath = $this->findMailboxMatching($mailBoxes, $category);

        Log::info("👉 $category");
        if (!$mailboxPath) {

            try {
                //code...
                $mailbox->createMailbox(Str::slug($category));
                $mailboxPath = 'INBOX.' . $category;
            } catch (\Throwable $th) {
                Log::error('❌', ['message' => $th->getMessage()]);
                $this->processingMessages[] = ["❌" => 'The mailbox could not be created'];
            }
        }

        //dd([...$mailboxCategory]);
        $mailbox->moveMail($id, $mailboxPath);
        $this->processingMessages[] = ["✅" => 'The message with id:' . $id . 'to category: ' . $category . 'was moved'];
        Log::info('Move the message with id:', ['id' => $id, 'category' => $category]);
    }




    /**
     * Perform the actions based on the extracted data
     * @param $action
     * @param $instructions
     * @param $messageId
     * @param $category
     * @return void
     */
    private function performActions($action, $instructions, $messageId, $settings = null): string
    {
        //dd($action, $instructions, $messageId, $settings);
        /*  //dd($action, $instructions, $messageId, $settings);
        if (!$action) {
            return back()->with('reply-generated', 'No action required.');
        } */
        Log::info('4️⃣ performActions', ['instructions' => $instructions, 'action' => $action, $messageId => $this->message]);

        // Generate a reply for the given message
        $this->reply[$messageId] = $this->generateReply($instructions);
        $this->processingMessages[] = ["✅" => "Reply generated"];

        //dd($this->reply[$messageId]);
        Log::info('👉Reply', ['reply' => $this->reply[$messageId]['message']['content']]);


        // TODO:
        // Refactor the method below
        //dd(trim($this->reply[$messageId]));
        $this->addCalendarEntryIfRequired($messageId, $instructions, $this->reply[$messageId]['message']['content']);

        return $this->reply[$messageId]['message']['content'];
    }


    public function addCalendarEntryIfRequired($messageId, $instructions, $replyMessageContent)
    {
        //dd($instructions, $replyMessageContent);
        $decoded = json_decode($replyMessageContent, true);
        $originalMessage = \App\Models\Message::where('message_identifier', $messageId)->first()?->toArray();
        //dd($instructions, $decoded, \App\Models\Message::where('message_identifier', $messageId)->first()->toArray());

        if ($instructions === 'insertEvent' || (is_array($instructions) && in_array('insertEvent', $instructions))) {


            //dd($decoded);
            $expected = config('responder.assistant.json_formats.withEvent');
            //dd($expected);
            $reviewResponse = $this->aiReviewResponse($decoded, $expected);
            //dd('Instructions', $instructions, 'Original Message', $originalMessage, 'Response: ', $decoded, 'Review: ', $reviewResponse);


            Log::info('📅 addCalendarEntryIfRequired', ['messageId' => $messageId, 'replyMessageContent' => $reviewResponse]);

            // get the requested datees fro mteh reply
            $startDateTime =   $reviewResponse['event'] ? Carbon::parse($reviewResponse['event']['start']['dateTime']) : now();
            $endDateTime = $reviewResponse['event'] ? Carbon::parse($reviewResponse['event']['end']['dateTime']) : now()->addHour();

            // check calendar availability
            $is_available = $this->checkCalendarAvailability($startDateTime, $endDateTime);
            $this->processingMessages[] = ["✅" => "Checking calendar availability"];

            //dd($is_available);
            // schedule the appointment if available or propose a different date/time
            if ($is_available) {
                $this->updateCalendar($messageId, $reviewResponse);
            }

            $this->processingMessages[] = ["✅" => "Calendar Event added"];
        }
    }


    /**
     * @param string $output the json output provided by the model
     * @param string $expectedOutputFormat the expected format of the output
     * @return array the decoded output in the expected format or false if not necesssary
     */
    public function aiReviewResponse($output, $expectedOutputFormat, $options = [])
    {

        $system = "Your task is to make sure the response contains the same keys defined in the expectedOutputFormat.\n
                    This task is critical for the success of the program so use the following reasoning process to generate your final answer. 1. Read the provided response enclosed within the response tags. 2. Read the expected output provided within the expectedOutputFormat tags. 3. Think if the response meets the expected format. Reflect about where the problem is. 4. Find the solution. 5. Reflect to check if the solution found is correct before providing your final answer. 6. Return your final answer to the user as JSON format. Don't show your reasoning process to the user and don't use it in your final answer.";
        $prompt = "
            Please check if the provided response below has the same keys expected in the expectedOutputFormat. If not fix it.
            Return your final response as json.
            <response>" .
            json_encode($output) .
            "</response> " .
            '<expectedOutputFormat>' .
            json_encode($expectedOutputFormat) .
            '</expectedOutputFormat>';

        $payload = [

            "model" => config('responder.assistant.model'),
            "stream" => false,
            'format' => 'json',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system,
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            ...$options
        ];

        //dd($payload);
        return json_decode($this->getResponse($payload)['message']['content'], true);
    }



    /**
     * Generate a reply for the given message
     * @param $messageId
     * @param $instructions
     * @return string
     */
    private function generateReply($instructions)
    {
        Log::info('5️⃣ Generate a reply...');
        //dd($instructions);
        //dd('reply to the message', $this->message);
        // prepare the payload to process the selected message
        $payload = $this->preparePayloadFrom($instructions);
        //dd($payload);
        // Use the payload to generate a response
        try {
            $response = $this->getResponse($payload); // Get the response
            //dd($response);
            $this->processingMessages[] = ["✅" => 'The reply was generated successfully'];
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('reply-generated', 'Error: Reply Not generated successfully');
        }
        //dd($instructions, $payload, $response);
        // inform the user that the generation was completed
        Log::info('🤖 Reply generated', ['reply' => $response]);
        return $response;
    }


    /**
     * TODO: EXPERIMENTAL - in progress: The knowledge base is injected
     * into the system for the generation of replies as is but it can grow and it is not
     * going to efficient because it could grow and make the model reach its token limits.
     * This should be refactored to a more solid solution maybe using RAG techniques.
     *
     * get the payload for the ollama api request
     *
     * @param $instructions
     * @returns array
     */
    private function preparePayloadFrom($instructions = null): array
    {

        if (is_array($instructions)) $instructions = join(',', $instructions);
        // if ($instructions) $message = ['role'=> 'user', 'content'=> "Instructions: $instructions"];


        // experiment: inject the knowledge base into the payload
        $knowledgeBase = Url::get(['url', 'content'])->toJson();

        //dd($instructions, $this->message, $knowledgeBase);
        return [
            'model' => Setting::where('key', 'selectedModel')->first()?->value ?? config('responder.assistant.model'),
            'stream' => false,
            'format' => 'json',
            'messages' => [

                [
                    'role' => 'system',
                    'content' => (htmlentities(Setting::where('key', 'assistantSystem')->first()?->value) ?? config('responder.assistant.system')) . "
                    ## Output format:
                    You must return your final response as JSON object, with the following keys:
                        - 'reply': string, the generated reply as text.
                        - 'event': false or the event object representation as described below under the ## example request section. " .
                        "
                    ## Available tools
                    To assist me better you can use the tools listed below delimited within '[tools][/tools]'.

                    [tools]
                    - 1. summarization
                    Create summaries of newsletters of similar emails to facilitate the user content's assimilation.
                    - 2. insert calendar events
                    - 3. knowledge base
                    [/tools]


                    When you process an email message, read the conversation, step back and think. Reflect to find out if it is necessary to check my calendar to verify the presence of an existing event or insert a new one. You can add events to the calendar for appointments, meetings, urgent tasks and other things that might need to be calendarized for me.

                    When you want to insert something into the calendar, you must format the response as json with also the \"event\" key.  The 'event' key can be false (when you don't want to add a calendar entry) or JSON representation of an event:
                        " . config('responder.assistant.tools.google.eventOutputFormat') .
                        "To improve your responses you can access the knowledge below." . "<knowledgeBase>" .
                        implode(' ',  Arr::flatten(json_decode($knowledgeBase, true))) .
                        "</knowledgeBase>"

                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->message) . "## Output format:
                    You must return your final response as JSON object, with the following keys:
                        - 'reply': a string containing the generated reply for the provided message.
                        - 'event': false or the event object representation as described below under the ## example request section. " . 'Action to take: ' . $instructions
                ]
            ]
        ];
    }
}
