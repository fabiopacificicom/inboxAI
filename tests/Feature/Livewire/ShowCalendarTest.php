<?php

use App\Livewire\ShowCalendar;
use Livewire\Livewire;

it('renders successfully', function () {

    Livewire::test(ShowCalendar::class, ['settings'=>[]] )
        ->assertSee('calendar');
})->group('calendar');


it('sets the period to the default value', function () {
    $livewire = Livewire::test(ShowCalendar::class, ['settings'=>[ 'filter' => 'day']]);
    $this->assertEquals('day', $livewire->period);
})->group('calendar');


it('sets the period to a non-default value if provided in settings', function ()
{
    $livewire = Livewire::test(ShowCalendar::class,[ 'settings' => ['filter' => 'week']]);
    $this->assertEquals('week', $livewire->period);
})->group('calendar');

it('mounts exising events from the connected calendar', function () {
    $livewire = Livewire::test(ShowCalendar::class,['settings'=>[]]);
    $this->assertIsArray($livewire->events);
})->group('calendar');
