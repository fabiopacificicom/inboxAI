<?php

namespace App\Livewire\AiReply;

use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ReplyFormComponent extends Component
{

    #[Reactive]
    public $reply;

    protected $rules = [
        'reply' => 'required',
    ];
    public function mount($reply)
    {
        $this->reply = $reply;
    }


    public function sendReply()
    {
        dd($this->reply);
        //$this->validate();

        // Logic to send the reply, such as calling an API or updating the database
        // ...

        // Reset the reply content after sending

        // Dispatch an event to notify other components that a reply has been sent
        //$this->dispatch('replySent', $this->messageId);

        // Optionally, display a success message or handle errors

    }

    public function render()
    {
        return view('livewire.ai-reply.reply-form-component');
    }
}
