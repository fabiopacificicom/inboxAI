<?php

use App\Livewire\MessageCardDialog;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(MessageCardDialog::class)
        ->assertStatus(200);
});
