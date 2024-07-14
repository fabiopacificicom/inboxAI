<?php

namespace App\Livewire\AiReply;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class OllamaSettings extends Component
{

    public $models;
    public $selectedModel;
    public $assistantSystem;
    public $ollamaServerAddress;


    public function boot()
    {
        $this->ollamaServerAddress = config('responder.assistant.server');
        $this->models = $this->getModels();
        //dd($this->models);
        $this->selectedModel = config('responder.assistant.model');
        $this->assistantSystem = config('responder.assistant.system');
    }

    public function render()
    {
        return view('livewire.ai-reply.ollama-settings');
    }

    public function getModels(){
        $response = Http::get(config('responder.assistant.tags'));
        return $response->json();

    }

}
