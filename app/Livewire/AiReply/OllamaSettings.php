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
        $this->selectedClassifier = $selectedClassifier;



        /* $this->ollamaServerAddress = Setting::where('key', 'ollamaServerAddress')->first()?->value ?? config('responder.assistant.server');
        $this->models = $this->getModels();

 */
        //dd($this->ollamaServerAddress);
    }

    public function render()
    {
        return view('livewire.ai-reply.ollama-settings');
    }

    public function getModels()
    {
        try {
            //code...
            $response = Http::get(config('responder.assistant.tags'));
            return $response->json();
            $this->connectionError = false;
        } catch (\Throwable $th) {
            //throw $th;
            session()->flash('message', $th->getMessage());
            $this->connectionError = true;
            Log::error($th->getMessage());
        }
    }

    public function updated($name, $value)
    {

        //dd($name);
        Setting::updateOrCreate(['key' => $name], ['value' => $value]);


        //dd($setting, $name, $value);
        if ($name === 'ollamaServerAddress') {
            // test the connection
            $this->checkOllamaConnection($value);
            // save in the settings table
        }


        //$this->dispatch($name, $value);
        //dd(Setting::all());

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
