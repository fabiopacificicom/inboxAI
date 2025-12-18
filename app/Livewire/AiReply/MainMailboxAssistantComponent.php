<?php

namespace App\Livewire\AiReply;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class MainMailboxAssistantComponent extends Component
{

    // messages processing properties
    public $messages = [];
    public $fetching = false;
    // model processor properties
    //public $models;
    public $selectedModel;
    public $assistantSystem;
    public $classifierSystem;
    public $selectedClassifier;

    public $ollamaServerAddress;

    public $settings;
    public function mount($settings)
    {
        $this->settings = $settings;
        // ollama server settings
        $this->ollamaServerAddress = $settings['ollamaServerAddress'] ?? config('responder.assistant.server');
        //$this->models = $this->getModels();
        //dd($this->models);
        $this->selectedModel = $settings['selectedModel'] ?? config('responder.assistant.model');
        $this->assistantSystem = $settings['assistantSystem'] ?? config('responder.assistant.system');
        $this->classifierSystem = $settings['classifierSystem'] ?? config('responder.classifier.system');
        $this->selectedClassifier = $settings['selectedClassifier'] ?? config('responder.classifier.model');

    }

    public function render()
    {
        return view('livewire.ai-reply.main-mailbox-assistant-component');
    }



}
