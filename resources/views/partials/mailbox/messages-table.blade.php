<div>
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th
                    class="w-24 px-5 py-3 border-b-2 border-gray-200 bg-gray-100 dark:bg-slate-700 dark:border-slate-800 dark:text-slate-400 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Date
                </th>


                <th
                    class="w-1/2 px-5 py-3 border-b-2 border-gray-200 bg-gray-100 dark:bg-slate-700 dark:border-slate-800 dark:text-slate-400 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Subject
                </th>
                <th
                    class="w-24 px-5 py-3 border-b-2 border-gray-200 bg-gray-100 dark:bg-slate-700 dark:border-slate-800 dark:text-slate-400 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Sender Details
                </th>
                <th
                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 dark:bg-slate-700 dark:border-slate-800 dark:text-slate-400 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            @if(!$messages == null)
            @forelse ($messages as $index => $message)
            <tr wire:key="{{ $message['message_identifier'] }}">
                <td class="w-24 px-5 py-5 border-b border-gray-200 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 text-sm">
                    {{ \Carbon\Carbon::parse($message['date'])->diffForHumans() }}
                </td>
                {{-- /date --}}
                <td class="w-1/2 px-5 py-5 border-b border-gray-200 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 text-sm">
                    <p>
                        {{ $message['subject'] }}
                    </p>
                    <small class="text-xs"><em>From:</em></small> {{ $message['from'] }} <br>

                </td>
                {{-- /subject  --}}
                <td class="w-24 px-5 py-5 border-b border-gray-200 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 text-sm text-center">

                    <button type="button" popoveraction="show" popovertarget="info-{{$message['id']}}">
                        <i class="bi bi-info-circle"></i>
                    </button>

                    <dialog id="info-{{$message['id']}}" class="text-left relative min-w-52 overflow-auto p-6 border border-gray-300 shadow-lg rounded-lg" popover>

                        <strong><em>Sender:</em></strong> {{ $message['sender'] }}<br>
                        <strong><em>From:</em></strong> {{ $message['from'] }} <br>

                        <strong><em>Reply to:</em></strong>
                        {{ Arr::join($message['reply_to_addresses'], ',') }}

                        <button type="button" class="absolute right-1 top-1" popoveraction="hide" popovertarget="info-{{$message['id']}}">
                            <i class="bi bi-x text-lg"></i>
                        </button>

                    </dialog>

                </td>
                {{-- /sender details --}}

                <td class="px-5 py-5 border-b border-gray-200 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 text-sm">


                    <div class="flex flex-wrap gap-1 justify-end">

                        <button id="process-message{{$message['message_identifier']}}"
                            title="Process message"
                            class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-gray-950 focus:outline-none focus:shadow-outline relative"
                            wire:click="processMessage('{{ $message['message_identifier'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="processMessage('{{ $message['message_identifier'] }}')"
                            wire:loading.class="opacity-50 cursor-not-allowed">
                            <span wire:loading.remove
                                wire:target="processMessage('{{ $message['message_identifier'] }}')">
                                <i class="bi bi-body-text"></i>
                                <span class="hidden lg:inline uppercase text-xs">
                                    Process
                                </span>
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
                            <span class="text-xs uppercase" wire:loading
                                wire:target="processMessage('{{ $message['message_identifier'] }}')">
                                In progress
                            </span>
                        </button>
                        {{-- /Process message button --}}


                        <button
                            wire:click="fetchMessage({{$message->message_identifier}})"
                            popovertarget="message-card-popover"
                            class="px-4 py-2 rounded-lg {{$message['content'] ? 'border border-green-600 bg-green-300 dark:bg-green-600/20 dark:text-green-600 text-green-950' : 'bg-gray-200 text-gray-600 dark:bg-slate-700 dark:text-slate-400'}}  hover:bg-dark-950 focus:outline-none focus:shadow-outline">
                            <i class="bi bi-arrows-angle-expand"></i>

                            <span class="hidden lg:inline">
                                Open
                            </span>
                        </button>
                        <!-- /.message-card-button -->



                        {{-- @include('partials.message.dialog-modal')--}}
                    </div>

                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5"
                    class="px-5 py-5 border-b border-gray-200 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 text-sm text-center">
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
            @endif
        </tbody>

        <tfoot class="py-3 text-gray-900 dark:text-slate-400">
            <tr>
                <td colspan="3" class="text-center py-3">
                    <button type="button" wire:click="loadMore()" wire:loading.attr="disabled">

                        <span wire:loading.class.add="hidden">
                            Load More Messages
                        </span>
                        <span wire:loading.class.remove="hidden" class="hidden">Loading</span>
                    </button>
                </td>
                <td>
                    {{__('Total Messages')}}: {{count($messages ?? [])}}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- <livewire:message-card-dialog></livewire:message-card-dialog> --}}

</div>