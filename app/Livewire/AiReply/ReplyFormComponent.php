<?php

namespace App\Livewire\AiReply;

use App\Mail\InboxAiReplyMailable;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ReplyFormComponent extends Component
{

    #[Reactive]
    public $reply;

    #[Validate('required|min:5')]
    public $content;

    public $message;

    protected $rules = [
        'reply' => 'required',
    ];
    public function mount($reply, $message)
    {
        $this->reply = $reply;
        //dd($reply);
        //$responseContent = json_decode($reply['message']['content'], true);
        //dd($responseContent);
        //$this->content = $responseContent['reply'];
        $this->message = $message;
    }



    public function updatedContent($value){
        $this->content = $value;
    }

    public function replyMessage()
    {
        //dd($this->content, $this->message, $this->reply);
        // Process the message
        //dd($this->reply[$messageId]);
        //dd($this->reply['message']['content']);
        $replyArray = json_decode($this->reply['message']['content'], true);
        //dd($replyArray);
        if (array_key_exists('category', $replyArray)) {
            $category = $replyArray['category'];
            // Apply classification
            $this->classify($this->message['messageId'], $category);
        }

        // add a calendar entry if necessary
        if (array_key_exists('event', $replyArray) && $replyArray['event']) {
            $this->calendar($replyArray['event']);
        }
    }



    public function classify($id, $category)
    {
        dd('classify', $id, $category);
    }

    public function calendar($event)
    {
        dd('calendar insert event', $event);
    }


    public function sendReply()
    {
        //dd($this->reply);
        $this->validate();

        //dd($this->content, $this->message, $this->reply);
        // sent the reply email
        Mail::to('admin@example.com')->send(new InboxAiReplyMailable($this->content, $this->message));
        // Dispatch an event to notify other components that a reply has been sent
        session()->flash('reply-sent', 'Message sent successfully');

        // Reset the reply content after sending
        //$this->content = '';
        // return back
        return back();
    }



    public function render()
    {
        return view('livewire.ai-reply.reply-form-component');
    }
}
