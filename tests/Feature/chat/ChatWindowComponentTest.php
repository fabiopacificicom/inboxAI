<?php

use App\Livewire\Chat\ChatWindowComponent;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseCount;

it('renders the chat on the dashboard', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user);

    $this->get('/dashboard')
        ->assertOk()
        ->assertSeeLivewire(ChatWindowComponent::class);
})->group('chat');

it('creates a conversation and persists user + assistant messages', function () {
    $user = User::factory()->create();

    Http::fake([
        '*' => Http::response([
            'message' => [
                'content' => 'Hello! How can I help with your inbox?'
            ],
        ], 200),
    ]);

    Livewire::actingAs($user);

    Livewire::test(ChatWindowComponent::class)
        ->set('userInput', 'Summarize my inbox')
        ->call('sendMessage');

    assertDatabaseCount('conversations', 1);
    assertDatabaseCount('chat_messages', 2);

    expect(Conversation::first()->user_id)->toBe($user->id);

    $assistant = ChatMessage::query()->where('role', 'assistant')->first();
    expect($assistant)->not->toBeNull();
    expect($assistant->tool_calls)->toBeNull();
})->group('chat');

it('executes tool calls and stores tool metadata', function () {
    $user = User::factory()->create();

    Http::fakeSequence()
        ->push([
            'message' => [
                'content' => '',
                'tool_calls' => [
                    [
                        'function' => [
                            'name' => 'get_account_stats',
                            'arguments' => [],
                        ],
                    ],
                ],
            ],
        ], 200)
        ->push([
            'message' => [
                'content' => 'Here are your account stats.'
            ],
        ], 200);

    Livewire::actingAs($user);

    Livewire::test(ChatWindowComponent::class)
        ->set('userInput', 'How many emails do I have?')
        ->call('sendMessage');

    $assistant = ChatMessage::query()->where('role', 'assistant')->latest('id')->first();
    expect($assistant)->not->toBeNull();
    expect($assistant->tool_calls)->toBeArray();
    expect($assistant->metadata)->toBeArray();
    expect($assistant->metadata['tool_results'] ?? null)->not->toBeNull();
})->group('chat');
