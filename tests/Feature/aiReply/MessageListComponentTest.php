<?php

use App\Livewire\AiReply\MessageListComponent;
use App\Models\User;
use App\Traits\Processable;
use Livewire\Livewire;

describe('🔥 Smoke test', function () {

    // check the component renders

    it('can render the component', function () {

        // create a user and act as
        $user = User::factory()->create();
        Livewire::actingAs($user);

        // hit the get route for the dashboard page and check if you see the component
        $this->get('/dashboard')
            ->assertSeeLivewire(MessageListComponent::class);
    });


    it('is rendering the correct view for the component', function () {
        Livewire::test(MessageListComponent::class, ['settings' => []])
            ->assertViewIs('livewire.ai-reply.message-list-component');
    });
})->group('messages');


describe('📨 Message Processing Unit', function () {

    it('can move a processed IMAP message to another mailbox folder', function () {

        // setup
        $anonymous = new class {
            use Processable;
        };

        // pick a message id and category to process

        $id = 604; // 604 is a message that is sent to the archive folder
        $category = 'INBOX.Archive';
        $settings = [];

        $anonymous->categorizeMessage($id, $category, $settings);
    })->skip('Skipping to avoid moving real messages');


    it('throws an exception when trying to move a message to a non-existent category', function () {
        // setup
        $anonymous = new class {
            use Processable;
        };

        // pick a message id and category to process
        $id = 604; // 604 is a message that is sent to the archive folder
        $category = 'INBOX.NonExistent';
        $settings = [];

        // assert that calling categorizeMessage throws an exception
        expect(fn() => $anonymous->categorizeMessage($id, $category, $settings))->toThrow('Exception', 'The provided category is not a vailad mailbox folder');
    });

})->group('messages');
