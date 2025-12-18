<?php

namespace Tests\Feature\chat;

use App\Models\User;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Livewire\Chat\ChatWindowComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessageEditingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_edit_their_own_messages()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $conversation = Conversation::create([
            'user_id' => $user->id,
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Original message',
        ]);

        Livewire::test(ChatWindowComponent::class)
            ->call('selectConversation', $conversation->id)
            ->call('editMessage', $message->id)
            ->assertSet('editingMessageId', $message->id)
            ->assertSet('editingContent', 'Original message')
            ->set('editingContent', 'Updated message')
            ->call('saveEdit')
            ->assertSet('editingMessageId', null)
            ->assertSet('editingContent', '');

        $this->assertEquals('Updated message', $message->fresh()->content);
    }

    /** @test */
    public function user_can_cancel_editing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $conversation = Conversation::create([
            'user_id' => $user->id,
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Original message',
        ]);

        Livewire::test(ChatWindowComponent::class)
            ->call('selectConversation', $conversation->id)
            ->call('editMessage', $message->id)
            ->assertSet('editingMessageId', $message->id)
            ->set('editingContent', 'Changed content')
            ->call('cancelEdit')
            ->assertSet('editingMessageId', null)
            ->assertSet('editingContent', '');

        // Original message should be unchanged
        $this->assertEquals('Original message', $message->fresh()->content);
    }

    /** @test */
    public function user_can_delete_messages()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $conversation = Conversation::create([
            'user_id' => $user->id,
        ]);

        $userMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'User message',
        ]);

        $assistantMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Assistant response',
        ]);

        Livewire::test(ChatWindowComponent::class)
            ->call('selectConversation', $conversation->id)
            ->call('deleteMessage', $userMessage->id);

        // Both user message and following assistant message should be deleted
        $this->assertDatabaseMissing('chat_messages', ['id' => $userMessage->id]);
        $this->assertDatabaseMissing('chat_messages', ['id' => $assistantMessage->id]);
    }

    /** @test */
    public function user_cannot_edit_assistant_messages()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $conversation = Conversation::create([
            'user_id' => $user->id,
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Assistant message',
        ]);

        Livewire::test(ChatWindowComponent::class)
            ->call('selectConversation', $conversation->id)
            ->call('editMessage', $message->id)
            ->assertSet('editingMessageId', null); // Should not set editing for assistant messages
    }
}
