<?php

namespace App\Livewire\AiReply;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class MainMailboxAssistantComponent extends Component
{

    public $host = 'mail.fabiopacifici.com';
    public $port = '993';
    public $encryption = 'ssl';
    public $username;
    public $password;
    public $filter = 'day'; // Default filter
    // messages processing properties
    public $messages = [];
    public $fetching = false;
    // model processor properties
    public $models;
    public $selectedModel;
    public $assistantSystem;
    public $ollamaServerAddress;

    public function mount()
    {
        // mailbox
        $this->username = config('responder.imap.username');

        // ollama server settings
        $this->selectedModel = config('responder.assistant.model');
        $this->assistantSystem = config('responder.assistant.system');
        $this->models = $this->getModels();
        $this->ollamaServerAddress = config('responder.assistant.server');
        $this->password = config('responder.imap.password');

    }

    public function render()
    {
        return view('livewire.ai-reply.main-mailbox-assistant-component');
    }

    public function getModels(){
        $response = Http::get(config('responder.assistant.tags'));
        return $response->json();

    }



}
