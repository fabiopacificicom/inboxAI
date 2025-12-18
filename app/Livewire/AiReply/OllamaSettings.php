<?php

namespace App\Livewire\AiReply;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Livewire\Attributes\Modelable;

use Livewire\Component;

class OllamaSettings extends Component
{

    public $models;
    #[Modelable]
    public $selectedModel;
    #[Modelable]
    public $selectedClassifier;

    public $assistantSystem;
    public $classifierSystem;
    public $ollamaServerAddress;
    public $connectionError = false;

    public function mount($ollamaServerAddress, $selectedModel, $assistantSystem, $classifierSystem, $selectedClassifier)
    {
        $this->ollamaServerAddress = $ollamaServerAddress;
        $this->models = $this->getModels();
        $this->selectedModel = $selectedModel;
        $this->assistantSystem = $assistantSystem;
        $this->classifierSystem = $classifierSystem;
    }

    public function render()
    {
        return view('livewire.ai-reply.ollama-settings');
    }

    public function getModels()
    {

        // retrieve the server address
        $server_address = $this->ollamaServerAddress . config('responder.assistant.tags');
        // get the access token incase required
        $accessToken = config('responder.assistant.server_api_token');
        // try to get the models from the server

        //dd($server_address, $accessToken);
        try {
            // return the response from the server as a json
            return Http::timeout(5000)
                ->withHeader('x-access-token', $accessToken)
                ->get($server_address)
                ->json();
            // set the connection error to false
            $this->connectionError = false;
        } catch (\Throwable $th) {
            session()->flash('message', $th->getMessage());
            $this->connectionError = true;
            Log::error($th->getMessage());
            return false;
        }
    }


    public function refreshModels()
    {

        //dd(config('responder.assistant.tags'), $this->ollamaServerAddress);
        $this->models = $this->getModels();
        //dd($this->models);
        $this->dispatch('models-updated')->self();
    }


    public function updated($name, $value)
    {

        //dd($name);
        Setting::updateOrCreate(['key' => $name], ['value' => $value]);


        //dd($setting, $name, $value);
        if ($name === 'ollamaServerAddress') {
            //dd($name, $value);
            // test the connection
            $this->checkOllamaConnection($value);

            // save in the settings table
            // update the models list
            $this->models = $this->getModels();
        }
    }


    public function checkOllamaConnection($address)
    {
        try {
            $response = Http::get($address);
            $this->connectionError = false;
        } catch (\Throwable $th) {
            $this->connectionError = true;
            Log::error($th->getMessage());
        }
    }
}
