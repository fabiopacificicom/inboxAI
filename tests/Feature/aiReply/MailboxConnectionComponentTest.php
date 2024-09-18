<?php

use App\Livewire\AiReply\MailboxConnectionComponent;
use Livewire\Livewire;
use App\Models\User;

it('renders correctly on the page', function () {

    // Create a user to act as
    $user = User::factory()->create();

    // Act as a logged in user
    Livewire::actingAs($user);

    // Get the page and assert that it contains the Livewire component
    $this->get('/dashboard')
        ->assertSeeLivewire(MailboxConnectionComponent::class);
})->group('mailbox');

it(
    'can fetch email messages from the connected mailbox and dispath an event',
    function () {
        Livewire::test(MailboxConnectionComponent::class, ['settings' => []])
            ->call('connectMailbox')
            ->assertDispatched('mailbox-sync-event');

            // assert database has been updated with a list of messages

    }
)->group('mailbox');
