<?php

namespace App\Traits;

use Spatie\GoogleCalendar\Event;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


trait Calendarable {
    private function checkCalendarAvailability($startDateTime, $endDateTime){


        /* Return true if available / false otherwise */

        $events = Event::get($startDateTime, $endDateTime);
        if ($events->count() === 0){
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
        $replyContent = json_decode($reply['message']['content'], true);


        if (!$replyContent['event']) {
            throw new \Exception("Missing event key in the provided response", 1);
        }
        Log::info('👉 Reply content', ['reply' => $replyContent]);
        //dd($replyContent['event'], $replyContent['reply']);

        //https://packagist.org/packages/spatie/laravel-google-calendar

        $event = new Event();
        $event->name = $replyContent['event']['summary'] ?? '';
        $event->description = $replyContent['event']['description'] ?? '';
        $event->startDateTime = Carbon::parse($replyContent['event']['start']['dateTime']);
        $event->endDateTime = Carbon::parse($replyContent['event']['end']['dateTime']);
        //$event->addAttendee($replyContent['event']['attendees'] ?? []);
        $event->save();
        Log::info('✅Event created', ['event' => $event]);
        //dd($event);

        return redirect()->back()->with('message', $replyContent['reply'] . 'event ' . $replyContent['event']['summary'] . 'was created');
    }

}
