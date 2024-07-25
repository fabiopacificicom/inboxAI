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
        $this->content = $reply['message']['content'];
        $this->message = $message;
    }


    public function sendReply()
    {
        //dd($this->reply);
        $this->validate();

        //dd($this->content, $this->message);
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
