<?php

namespace App\Livewire\AiReply;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


use Livewire\Component;

class OllamaSettings extends Component
{

    public $models;
    public $selectedModel;
    public $assistantSystem;
    public $ollamaServerAddress;
    public $connectionError = false;

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

    public function updated($name, $value)
    {
        //dd($name, $value);
        if ($name === 'ollamaServerAddress') {
            // test the connection
            $this->checkOllamaConnection($value);
            // save in the settings table

        }
    }


    public function checkOllamaConnection($address){
        try {
            $response = Http::get($address);
            $this->connectionError = false;

        } catch (\Throwable $th) {
            $this->connectionError = 'Connection Failed. Check the logs for more details.';
            Log::error($th->getMessage());
        }

    }

}
