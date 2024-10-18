<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Traits\HasMailboxConnection;
use App\Traits\Helpers;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Reactive;

class MessageCardDialog extends Component
{

    use HasMailboxConnection, Helpers;


    public $message;

    public function render()
    {

        return view('livewire.message-card-dialog');
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
        $content =  $this->convertHtmlToPlainText($content);
        //dd($content);
        $this->fetching = false;

        // find the mesage from the db
        $message = Message::where('message_identifier', $id)->first();
        //update it
        $message->update(['content' => ($content)]);

        // update the messages collection
        //$this->messages = Cache::get('messages', $this->retreiveLatestMessages());
    }
}
