
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
                                        class="px-4 py-2 rounded bg-blue-500 text-white hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                                        x-on:click="show = !show">
                                        View
                                    </button>

                                    <div class="modal-backdrop bg-gray-900 bg-opacity-50 absolute inset-0 flex justify-center items-center"
                                        x-show="show" style="display: none;">
                                        <div class="modal-card bg-white p-6 rounded-lg shadow-xl">
                                            <div class="flex justify-between items-center">
                                                <button
                                                    class="px-4 py-2 rounded bg-green-500 text-white hover:bg-green-700 focus:outline-none focus:shadow-outline relative"
                                                    wire:click="generateReplyFor('{{ $message['messageId'] }}')"
                                                    wire:loading.attr="disabled" wire:target="generateReplyFor('{{ $message['messageId'] }}')"
                                                    wire:loading.class="opacity-50 cursor-not-allowed">
                                                    <span wire:loading.remove wire:target="generateReplyFor('{{ $message['messageId'] }}')">
                                                        Generate Reply
                                                    </span>
                                                    <span wire:loading wire:target="generateReplyFor('{{ $message['messageId'] }}')"
                                                        class="absolute left-0 right-0 mx-auto">
                                                        <svg class="animate-spin h-5 w-5 mr-3"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12"
                                                                cy="12" r="10" stroke="currentColor"
                                                                strokeWidth="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z">
                                                            </path>
                                                        </svg>
                                                    </span>
                                                    <span wire:loading wire:target="generateReplyFor('{{ $message['messageId'] }}')">
                                                        In progress
                                                    </span>
                                                </button>
                                                <button
                                                    class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-700 focus:outline-none focus:shadow-outline"
                                                    x-on:click="show = !show">
                                                    Close
                                                </button>
                                            </div>

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
                                                <div class="mt-2 text-gray-800 text-sm">
                                                    {!! $message['content'] !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                Nothing here
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
