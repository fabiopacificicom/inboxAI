<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Traits\HasMailboxConnection;
use App\Traits\Helpers;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Reactive;
use App\Traits\Processable;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class MessageCardDialog extends Component
{

    use HasMailboxConnection, Helpers, Processable;


    public $message;
    public $settings;

    public function mount()
    {
        $this->settings = Setting::all(['key', 'value'])->mapWithKeys(function ($item) {
            return [$item['key'] => $item['value']];
        });
    }


    public function render()
    {

        $data = [
            'settings' => $this->settings
        ];
        return view('livewire.message-card-dialog', $data);
    }


    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            LOADING ...
        </div>
        HTML;
    }



    /**
     * Fetch a message from the imap server and update the corresponding model
     * in the db
     * @param $id - the id of the message to fetch
     * @return void
     */
    #[On('fetch-message')]
    public function fetchMessage($id): void
    {
        $this->processingMessages = [];

        $this->message = '';
        //dd($id);
        // get the message to fetch
        $this->message = Message::where('message_identifier', $id)->first();

        //dd($this->message);
        //$this->loading = true;
        // get the message from the imap server
        $imapMailbox = $this->makeMailboxFromSettings(inbox: $this->selectedMailbox);

        $mail = $imapMailbox->getMail($id);
        //dd($mail);
        $content = $mail?->textPlain;

        if (strlen($content)  == 0) {
            $content = $mail?->textHtml;
        }
        //dd($content);
        $cleanedContent =  preg_replace("/[^A-Za-z0-9 ]/", '', $this->convertHtmlToPlainText($content));
        //dd($content);
        $this->fetching = false;

        // find the mesage from the db
        $message = Message::where('message_identifier', $id)->first();
        //update it
        $message->update(['content' => $cleanedContent]);

        // update the messages collection
        //$this->messages = Cache::get('messages', $this->retreiveLatestMessages());
    }


    /**
     * InboxAI: Process the given message with AI
     *
     * This method is responsible of performing classification
     * and other actions based on the result.
     *
     * @param string $messageId the id of the message retrieved from the imap server
     * @return void
     */
    public function processMessage($messageId): void
    {

        // sets the processing messages to an empty array
        $this->processingMessages = [];

        // 2. classify the message for further processing
        [$action, $instructions] = $this->classify($this->message);

        // 4. Perform the actions on the message based on the action
        $reply = $this->performActions($action, $instructions, $messageId, $this->settings);
        //dd($reply);
        $message_content = 'Subject:' . $this->message['subject'] . '. Body: ' . $this->message['content'];

        $data =  [
            'message_identifier' => $messageId,
            'message_content' => $message_content,
            'response_content' => trim(json_decode($reply, true)['reply'])
        ];


        $this->message->replies()->create($data);
        Log::info("reply created for message $messageId", ['reply' => $reply]);
    }
}
