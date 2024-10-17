<?php

namespace App\Livewire\AiReply;

use App\Mail\InboxAiReplyMailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ReplyFormComponent extends Component
{

    //#[Reactive]
    public $reply = [];

    #[Validate('required|min:5')]
    public $content;

    public $message;

    protected $rules = [
        'reply' => 'required',
    ];
    public function mount($message)
    {
        //dd($message);
        $this->message = $message;
    }


    #[On('set-reply')]
    public function setReply($reply)
    {
        //dd($reply);
        //dd($this->message->replies);
        $this->reply = $reply;
        //dd($this->reply);
        $messageContent = json_decode($reply, true);
        if (array_key_exists('reply', $messageContent)) {
            $this->content = $messageContent['reply'];
        }
        $this->reply = $reply;
    }

    #[On('set-content')]
    public function setContent($content)
    {
        //dd($content);
        $this->content = $content;
    }

    public function updatedContent($value)
    {
        //dd($value);
        $this->content = $value;
    }

    public function sendReply()
    {
        //dd($this->reply);
        $this->validate();

        //dd($this->content, $this->message, $this->reply);
        // sent the reply email
        Mail::to($this->message->reply_to_addresses)->send(new InboxAiReplyMailable($this->content, $this->message));
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
