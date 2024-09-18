<?php

namespace App\Feature\Livewire\AiReply;

use Livewire\Livewire;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Livewire\AiReply\OllamaSettings;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('renders correctly on the page', function () {

    // Create a user to act as
    $user = User::factory()->create();

    // Act as a logged in user
    Livewire::actingAs($user);

    // Get the page and assert that it contains the Livewire component
    $this->get('/dashboard')
        ->assertSeeLivewire(OllamaSettings::class);
})->group('settings');

it('returns an array of models from the API', function () {

    $livewire = Livewire::test(
        OllamaSettings::class,
        [
            'ollamaServerAddress',
            'http://127.0.0.1:11434/api/chat',
            'assistantSystem' => 'You are an helpful assistant',
            'classifierSystem' => 'You are an helpful classifier.',
            'selectedClassifier' => '',
        ]
    )->call('getModels')
        ->assertViewHas('models', function ($models) {
            return count($models) > 0;
        });
})->group('settings');


it('updates the settings table when a value is changed', function () {
    $livewire = Livewire::test(OllamaSettings::class, [
        'ollamaServerAddress' => 'http://127.0.0.1:11434/api/chat',
        'assistantSystem' => 'You are an helpful assistant',
        'classifierSystem' => 'You are an helpful classifier.',
        'selectedClassifier' => '',
        'selectedModel' => '',

    ])
        ->set('ollamaServerAddress', 'https://example.com');

    $this->assertDatabaseHas('settings', [
        'key' => 'ollamaServerAddress',
        'value' => 'https://example.com',
    ]);
})->group('settings');


it('shows a connection error message when the API is unreachable', function () {

    $livewire = Livewire::test(
        OllamaSettings::class,
        [
            'ollamaServerAddress' => 'http://127.0.0.1:11434/api/chat',
            'assistantSystem' => 'You are an helpful assistant',
            'classifierSystem' => 'You are an helpful classifier.',
            'selectedClassifier' => '',
            'selectedModel' => '',

        ]
    )
        ->set('ollamaServerAddress', 'https://example.com');

        $livewire->call('checkOllamaConnection', ['address'=> $livewire->ollamaServerAddress])
        ->assertViewHas('connectionError');
})->group('settings');

