<?php

namespace App\Livewire\AiReply;

use Livewire\Component;

class MessageListComponent extends Component
{

    public $messages;

    public function mount($messages)
    {
        $this->messages = $messages;
    }
    public function render()
    {
        return view('livewire.ai-reply.message-list-component');
    }
}
