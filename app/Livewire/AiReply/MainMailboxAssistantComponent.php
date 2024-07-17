<?php

namespace App\Livewire\AiReply;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
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
        $this->password = config('responder.imap.password');

        // ollama server settings
        $this->ollamaServerAddress = Setting::where('key', 'ollamaServerAddress')->first()?->value ?? config('responder.assistant.server');
        $this->models = $this->getModels();
        $this->selectedModel = Setting::where('key', 'selectedModel')->first()?->value ?? config('responder.assistant.model');
        $this->assistantSystem = Setting::where('key', 'assistantSystem')->first()?->value ?? config('responder.assistant.system');


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
