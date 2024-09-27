<?php

use App\Livewire\KnowledgeBaseComponent;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(KnowledgeBaseComponent::class)
        ->assertStatus(200);
});
