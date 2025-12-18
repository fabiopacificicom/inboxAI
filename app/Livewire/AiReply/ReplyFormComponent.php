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
use App\Models\Message;
use App\Models\Reply;

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

        $this->message = $message;
        //Log::info('Reply Form, id:' . $message->id, ['message' => $this->message->replies->first()]);
        $this->content = $this->message?->replies->first()?->response_content;
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

    #[On('set-reply-content')]
    public function setContent($id)
    {
        //dd($id);
        $reply_content = Reply::find(intval($id))->response_content;
        //dd($reply_content);
        $this->content = $reply_content;
    }

    public function updatedContent($value)
    {
        //dd($value);
        $this->content = $value;
    }

    public function sendReply()
    {
        $this->validate();
        //dd($this->content);

        //dd($this->content, $this->message, $this->reply);
        // sent the reply email
        Mail::to($this->message->reply_to_addresses)->send(new InboxAiReplyMailable($this->content, $this->message));
        // Dispatch an event to notify other components that a reply has been sent
        // Reset the reply content after sending
        //$this->content = '';
        // return back
        return back()->with('reply-sent', 'Message sent successfully');
    }



    public function render()
    {
        return view('livewire.ai-reply.reply-form-component');
    }
}
