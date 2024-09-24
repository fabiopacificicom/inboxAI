<div class="grid grid-cols-1 sm:flex max-w-7xl mx-auto">

    <div class="sm:w-56 p-4">
        <ul>
            @if (!empty($mailboxes))
                @foreach ($mailboxes as $index => $box)
                    {{-- @dd($box) --}}
                    <li wire:key="folder-{{ $index }}" class="hover:border-b-2 hover:border-gray-700"
                        wire:click="switchMailboxFolder({{ json_encode($box) }})">

                        <button type="button"
                            class="w-full text-start rounded-md p-2 {{ $selectedMailbox == $box['shortpath'] ? 'bg-gray-800 text-white' : '' }}">
                            {{ preg_replace('/^INBOX\.(.*)$/', '$1', $box['shortpath']) }}
                        </button>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>


    <div id="inbox-table" class="bg-white shadow-sm sm:rounded-lg w-full">

        <header id="top_toolbar" class="flex justify-between">

            <div class="flex gap-1 items-center">
                <button type="button" wire:click="deleteMessages(false)"
                    class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
                    🗑️ Trash All
                </button>
                <button type="button" wire:click="cleanTrash()"
                    class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
                    🗑️ Clean Trash
                </button>
                <button type="button" wire:click="deleteMessages(true)"
                    wire:confirm="Are you sure? This will delete all messages below from the imap server - this is not reversible."
                    class="bg-gray-100 hover:bg-gray-300 p-2 rounded-lg m-1">
                    ❌ Delete All
                </button>
            </div>



            <div class="filters flex gap-1 items-center">

                {{-- Limit results --}}
                <div id="limit">
                    <label for="limit" class="sr-only block text-sm font-medium text-gray-700">
                        Limit results to:
                    </label>
                    <input type="number" name="limit" id="limit" min="1" max="50"
                        wire:model.blur="limit"
                        class="w-auto min-w-36 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        placeholder="Per page">
                </div>

                {{-- Filter email fetch backdating --}}
                <div class="period">
                    <label for="period" class="sr-only block text-sm font-medium text-gray-700">Filter results
                        by:</label>
                    <select id="period" wire:model.live="filter"
                        class="w-auto min-w-28 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="" disabled selected>Select a period...</option>
                        <option value="day">Day</option>
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                    </select>
                </div>
            </div>
        </header>




        @if ($fetching)
            <div class="flex justify-center items-center h-full">
                <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-12 w-12"></div>
            </div>
        @else
            <div class="messages overflow-x-auto">

                {{-- Messages table --}}
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Sender Details
                            </th>

                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Subject
                            </th>
                            <th
                                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dd($messages) --}}
                        @forelse ($messages as $index => $message)
                            {{-- @dd($message) --}}
                            <tr wire:key="{{ $message['message_identifier'] }}">
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ \Carbon\Carbon::parse($message['date'])->diffForHumans() }}
                                </td>
                                {{-- /date --}}
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">

                                    <strong><em>Sender:</em></strong> {{ $message['sender'] }}<br>
                                    <strong><em>From:</em></strong> {{ $message['from'] }} <br>
                                    <strong><em>Reply to:</em></strong>
                                    {{ Arr::join($message['reply_to_addresses'], ',') }}
                                </td>
                                {{-- /sender details --}}
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $message['subject'] }}
                                </td>
                                {{-- /subject  --}}
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">


                                    <button wire:click="fetchMessage({{ $message['message_identifier'] }})"
                                        popovertarget="message-popover-{{ $index }}"
                                        class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-dark-950 focus:outline-none focus:shadow-outline">
                                        Open
                                    </button>
                                    <dialog popover id="message-popover-{{ $index }}"
                                        class="max-w-7xl m-auto bg-white p-6 rounded-lg shadow-xl">


                                        <header class="modal-header flex justify-between items-center">
                                            <button
                                                class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-gray-950 focus:outline-none focus:shadow-outline relative"
                                                wire:click="processMessage('{{ $message['message_identifier'] }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="processMessage('{{ $message['message_identifier'] }}')"
                                                wire:loading.class="opacity-50 cursor-not-allowed">
                                                <span wire:loading.remove
                                                    wire:target="processMessage('{{ $message['message_identifier'] }}')">
                                                    Process Message
                                                </span>
                                                <span wire:loading
                                                    wire:target="processMessage('{{ $message['message_identifier'] }}')"
                                                    class="absolute left-0 right-0 mx-auto">
                                                    <svg class="animate-spin h-5 w-5 mr-3"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" strokeWidth="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z">
                                                        </path>
                                                    </svg>
                                                </span>
                                                <span wire:loading
                                                    wire:target="processMessage('{{ $message['message_identifier'] }}')">
                                                    In progress
                                                </span>
                                            </button>
                                            {{-- /Process message button --}}

                                            <button popovertarget="message-popover-{{ $index }}"
                                                popovertargetaction="hide"
                                                class="px-4 py-2 rounded bg-gray-200 text-gray-600 hover:bg-gray-100 focus:outline-none focus:shadow-outline">
                                                Close
                                            </button>

                                        </header>
                                        {{-- /.modal-header --}}

                                        @if ($fetching == true)
                                            <div>LOADING MESSAGE...</div>
                                        @endif

                                        <div wire:key="message-{{ $index }}" class="mt-4">
                                            @if (array_key_exists($message['message_identifier'], $reply))
                                                @livewire(
                                                    'ai-reply.reply-form-component',
                                                    [
                                                        'reply' => $reply[$message['message_identifier']],
                                                        'message' => $message,
                                                    ],
                                                    key($message['message_identifier'])
                                                )
                                            @endif
                                            {{-- /Livewire ai-reply.reply-form-component --}}

                                            @include('partials.processing-messages')

                                        </div>

                                        <div class="metadata py-4">
                                            <p class="text-gray-900 leading-none">
                                                Message ID: {{ $message['message_identifier'] }}
                                            </p>
                                            <p class="text-gray-600">
                                                Sender: {{ $message['sender'] }}
                                            </p>
                                        </div>
                                        {{-- /.metadata --}}


                                        <div class="received-message mt-4">
                                            <h3 class="text-lg font-semibold">Original</h3>
                                            <div class="mt-2 text-gray-800 text-sm" class="text-gray-800">
                                                <!-- Blade Template -->
                                                @php
                                                    $message_identifier =
                                                        'emailIframe-' . md5($message['message_identifier']);
                                                @endphp
                                                <div class="overflow-y-auto h-32"
                                                    id="wrapper-{{ $message_identifier }}">
                                                    {{ $message['content'] }}
                                                </div>

                                            </div>
                                        </div>
                                        {{-- /.received-message --}}


                                    </dialog>




                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <button class="bg-gray-900 text-white p-2 hover:bg-gray-700 rounded hover:shadow"
                                        wire:click="$dispatch('sync-mailbox')" wire:loading.attr="disabled">
                                        <div class="mx-auto flex gap-2 items-center" wire:loading.remove>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-binoculars-fill"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M4.5 1A1.5 1.5 0 0 0 3 2.5V3h4v-.5A1.5 1.5 0 0 0 5.5 1zM7 4v1h2V4h4v.882a.5.5 0 0 0 .276.447l.895.447A1.5 1.5 0 0 1 15 7.118V13H9v-1.5a.5.5 0 0 1 .146-.354l.854-.853V9.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v.793l.854.853A.5.5 0 0 1 7 11.5V13H1V7.118a1.5 1.5 0 0 1 .83-1.342l.894-.447A.5.5 0 0 0 3 4.882V4zM1 14v.5A1.5 1.5 0 0 0 2.5 16h3A1.5 1.5 0 0 0 7 14.5V14zm8 0v.5a1.5 1.5 0 0 0 1.5 1.5h3a1.5 1.5 0 0 0 1.5-1.5V14zm4-11H9v-.5A1.5 1.5 0 0 1 10.5 1h1A1.5 1.5 0 0 1 13 2.5z" />
                                            </svg>
                                            Nothing to see here, sync your inbox now.
                                        </div>

                                        <div wire:loading.class.remove="hidden" class="hidden">almost done...</div>

                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>


</div>
