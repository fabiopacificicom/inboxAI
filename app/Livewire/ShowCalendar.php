<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting;
use Spatie\GoogleCalendar\Event;

class ShowCalendar extends Component
{
    public $period = 'Week';
    public $events = [];
    public function render()
    {
        return view('livewire.show-calendar');
    }


    public function mount()
    {
        $this->period = Setting::where('key', 'filter')->first()?->value ?? 'day';
        $this->getEvents();
        // dd($this->events);
    }

    public function updatedPeriod()
    {
        $this->getEvents();
    }

    private function getEvents()
    {
        switch ($this->period) {
            case 'Week':
                $events = Event::get(now(), now()->addWeek());
                # week
                break;
            case 'Month':
                # month
                $events = Event::get(now(), now()->addMonth());
                break;
            default:
                # year
                $events = Event::get(now(), now()->addYear());
                break;
        }
        $this->events = array_map(fn($event) => $event['googleEvent'], json_decode($events, true));
    }
}
