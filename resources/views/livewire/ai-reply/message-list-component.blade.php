<div id="inbox-table" class="min-h-screen bg-gray-100 p-5">
    {{-- Messages table --}}
    @if ($fetching)
        <div class="flex justify-center items-center h-full">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-12 w-12"></div>
        </div>
    @else
        <div class="messages bg-white shadow overflow-hidden rounded-lg">
            @if (session()->has('message'))
                <div class="alert bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative"
                    role="alert">
                    {{ session('message') }}
                </div>
            @endif

            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Sender
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Reply To
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
                    @forelse ($messages as $message)
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ \Carbon\Carbon::parse($message['date'])->diffForHumans() }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ $message['sender'] }}
                                {{ $message['from'] }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ Arr::join($message['replyToAddresses'], ',') }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{ $message['subject'] }}
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <div x-data="{ show: false }">
                                    <button
                                        class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-dark-950 focus:outline-none focus:shadow-outline"
                                        x-on:click="show = !show">
                                        View
                                    </button>

                                    <div class="modal-backdrop bg-gray-900 bg-opacity-50 absolute inset-0 flex justify-center items-center"
                                        x-show="show" style="display: none;">
                                        <div class="modal-card bg-white p-6 rounded-lg shadow-xl">
                                            <div class="flex justify-between items-center">
                                                <button
                                                    class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-gray-950 focus:outline-none focus:shadow-outline relative"
                                                    wire:click="processMessage('{{ $message['messageId'] }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="processMessage('{{ $message['messageId'] }}')"
                                                    wire:loading.class="opacity-50 cursor-not-allowed">
                                                    <span wire:loading.remove
                                                        wire:target="processMessage('{{ $message['messageId'] }}')">
                                                        Process Message
                                                    </span>
                                                    <span wire:loading
                                                        wire:target="processMessage('{{ $message['messageId'] }}')"
                                                        class="absolute left-0 right-0 mx-auto">
                                                        <svg class="animate-spin h-5 w-5 mr-3"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z">
                                                            </path>
                                                        </svg>
                                                    </span>
                                                    <span wire:loading
                                                        wire:target="processMessage('{{ $message['messageId'] }}')">
                                                        In progress
                                                    </span>
                                                </button>
                                                <button
                                                    class="px-4 py-2 rounded bg-gray-200 text-gray-600 hover:bg-gray-100 focus:outline-none focus:shadow-outline"
                                                    x-on:click="show = !show">
                                                    Close
                                                </button>
                                            </div>

                                            @if (array_key_exists($message['messageId'], $reply))
                                                @livewire(
                                                    'ai-reply.reply-form-component',
                                                    [
                                                        'reply' => $reply[$message['messageId']],
                                                        'message' => $message
                                                    ],
                                                    key($message['messageId'])
                                                )
                                            @endif

                                            <div class="py-4">
                                                <p class="text-gray-900 leading-none">
                                                    Message ID: {{ $message['messageId'] }}
                                                </p>
                                                <p class="text-gray-600">
                                                    Sender: {{ $message['sender'] }}
                                                </p>
                                            </div>

                                            <div class="original mt-4">
                                                <h3 class="text-lg font-semibold">Original</h3>
                                                <div class="mt-2 text-gray-800 text-sm" class="text-gray-800">
                                                    <!-- Blade Template -->
                                                    @php
                                                        $messageId = 'emailIframe-' . md5($message['messageId']);
                                                    @endphp
                                                    <div class="overflow-y-auto h-32" id="wrapper-{{ $messageId }}">
                                                        {{ $message['content'] }}
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <button
                                    class="bg-gray-900 text-white p-2 hover:bg-gray-700 rounded hover:shadow"
                                    wire:click="$dispatch('sync-mailbox')" wire:loading.attr="disabled">
                                    <div class="mx-auto flex gap-2 items-center" wire:loading.remove>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-binoculars-fill" viewBox="0 0 16 16">
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
