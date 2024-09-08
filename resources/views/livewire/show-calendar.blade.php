<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}

    <style>
        #calendar:popover-open {
            width: 50%;
            max-width: 360px;
            height: 100dvh;
            position: fixed;
            inset: unset;
            bottom: 5px;
            right: 5px;
            margin: 0;

            @media (min-width: 992px) {
                max-width: 600px
            }
        }
    </style>
    <button popovertarget="calendar" popovertargetaction="show"
        class="flex items-center gap-1 p-3 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-calendar3-week" viewBox="0 0 16 16">
            <path
                d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
            <path
                d="M12 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-5 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m2-3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-5 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
        </svg>
        <span class="text-xs">{{ __('Calendar') }}</span>
    </button>
    <div id="calendar" popover class="bg-white p-4 rounded-lg shadow-lg fixed top-0 right-0">
        <div class="flex justify-between">
            <h3 class="text-gray-800 text-lg">Calendar ({{ $period }})</h3>

            <select name="period" id="period" wire:model.live="period" class="border-none">
                <option value="Week">Next Week</option>
                <option value="Month">Next Month</option>
                <option value="Year">Next Year</option>
            </select>
        </div>

        @forelse ($events as $event)
            <div class="calendar_entry hover:bg-gray-100 border-b-2 p-2 lg:p-4 rounded-lg relative">
                {{-- dd($event, $event->startDateTime->diffForHumans(), $event->htmlLink) --}}
                <p>{{ $event['summary'] }}</p>
                <p>{{ $event['description'] }}</p>
                <small class="text-xs">starts in </small>
                <a href="{{ $event['htmlLink'] }}" target="_blank"
                    class="block text-xs uppercase underline absolute top-1 end-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5" />
                        <path fill-rule="evenodd"
                            d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z" />
                    </svg>
                </a>
            </div>
        @empty
            <div>Nothing to show.</div>
        @endforelse

    </div>

</div>
