<?php

namespace App\Traits;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

trait HandleAiResponse {
    /**
     * Get the ollama response for the given payload
     * @returns array the http response as an array
     */
    private function getResponse($payload): array
    {

        $response = Http::timeout(5000)
            ->post(Setting::where('key', 'ollamaServerAddress')
                ->first()?->value ?? config('responder.assistant.server'), $payload);

        $response->onError(function ($message) {
            Log::error('❌ Error: ' . $message);
            throw new \Exception($message, 1);
        });

        //dd($response->json());
        return $response->json();
    }
}
