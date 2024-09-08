<?php

use App\Livewire\ShowCalendar;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ShowCalendar::class)
        ->assertStatus(200);
});
