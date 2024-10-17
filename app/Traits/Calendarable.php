<?php

namespace App\Traits;

use Spatie\GoogleCalendar\Event;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


trait Calendarable
{
    private function checkCalendarAvailability($startDateTime, $endDateTime)
    {


        /* Return true if available / false otherwise */

        $events = Event::get($startDateTime, $endDateTime);
        if ($events->count() === 0) {
            return true;
        }
        return false;
        //dd($events->count() === 0, $startDateTime, $endDateTime);
    }


    #[On('reply-generated')]
    /**
     * TODO: MessageId is not used, if not required condider to remove it
     * Update the calendar
     * @param array $reply
     * @return void
     */
    public function updateCalendar($messageId, $reply)
    {


        Log::info('6️⃣ Updating calendar for the previous reply');
        /* TODO:
        Handle the reply here, inside the reply thereis the google calendar event json
        to use with the spatie package to insert calendar events. */
        //dd($messageId, $reply);
        //$replyContent = json_decode($reply, true)#;


        if (!$reply['event']) {
            throw new \Exception("Missing event key in the provided response", 1);
        }
        Log::info('Reply content for the 📅', ['reply' => $reply]);
        //dd($reply['event'], $reply['reply']);

        //https://packagist.org/packages/spatie/laravel-g#oogle-calendar

        $event = new Event();
        $event->name = $reply['event']['summary'] ?? '';
        $event->description = $reply['event']['description'] ?? '';
        $event->startDateTime = Carbon::parse($reply['event']['start']['dateTime']);
        $event->endDateTime = Carbon::parse($reply['event']['end']['dateTime']);
        //$event->addAttendee($reply['event']['attendees'] ?? []);
        $event->save();
        Log::info('✅Event created', ['event' => $event]);
        //dd($event);

        return redirect()->back()->with('message', $reply['reply'] . 'event ' . $reply['event']['summary'] . 'was created');
    }
}
