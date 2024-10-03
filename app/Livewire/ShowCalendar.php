<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting;
use Spatie\GoogleCalendar\Event;

class ShowCalendar extends Component
{
    public $period;
    public $events = [];
    public function render()
    {
        return view('livewire.show-calendar');
    }


    public function mount($settings)
    {

        $this->period = $settings['filter'] ?? 'day';
        $this->getEvents();
        // dd($this->events);
    }

    public function updatedPeriod()
    {
        $this->getEvents();
    }

    private function getEvents()
    {
        switch (strtolower($this->period)) {
            case 'week':
                $events = Event::get(now(), now()->addWeek());
                # week
                break;
            case 'month':
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
