<?php

use App\Livewire\AiReply\OllamaSettings;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

it('displays success message when assistant system setting is updated', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OllamaSettings::class, [
            'ollamaServerAddress' => 'http://localhost:11434',
            'models' => ['models' => []],
            'selectedModel' => 'llama3.1',
            'assistantSystem' => 'You are a helpful assistant',
        ])
        ->set('assistantSystem', 'You are an email assistant')
        ->assertSet('successMessage', 'Settings saved successfully');
});

it('saves assistant system setting to database', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OllamaSettings::class, [
            'ollamaServerAddress' => 'http://localhost:11434',
            'models' => ['models' => []],
            'selectedModel' => 'llama3.1',
            'assistantSystem' => 'You are a helpful assistant',
        ])
        ->set('assistantSystem', 'You are an email assistant');

    expect(Setting::where('key', 'assistantSystem')->first()->value)
        ->toBe('You are an email assistant');
});

it('displays success message when selected model is updated', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OllamaSettings::class, [
            'ollamaServerAddress' => 'http://localhost:11434',
            'models' => ['models' => []],
            'selectedModel' => 'llama3.1',
            'assistantSystem' => 'You are a helpful assistant',
        ])
        ->set('selectedModel', 'gemma')
        ->assertSet('successMessage', 'Settings saved successfully');
});

it('displays success message when ollama server address is updated', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OllamaSettings::class, [
            'ollamaServerAddress' => 'http://localhost:11434',
            'models' => ['models' => []],
            'selectedModel' => 'llama3.1',
            'assistantSystem' => 'You are a helpful assistant',
        ])
        ->set('ollamaServerAddress', 'http://localhost:11435')
        ->assertSet('successMessage', 'Settings saved successfully');
});
