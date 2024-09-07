<div>

    @if (session('message'))
        <div class="alert bg-red-300 text-red-900 p-2 rounded">
            {{ session('message') }}
        </div>
    @endif



    <div id="settings" x-data="{ open: false }">
        <div class="flex justify-between items-center mb-4">

            {{-- Mailbox settings --}}
            <button @click="open = !open"
                class="flex items-center gap-2 bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                {{ __('Settings') }}
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-sliders2-vertical" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M0 10.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H3V1.5a.5.5 0 0 0-1 0V10H.5a.5.5 0 0 0-.5.5M2.5 12a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2a.5.5 0 0 0-.5-.5m3-6.5A.5.5 0 0 0 6 6h1.5v8.5a.5.5 0 0 0 1 0V6H10a.5.5 0 0 0 0-1H6a.5.5 0 0 0-.5.5M8 1a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2A.5.5 0 0 0 8 1m3 9.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H14V1.5a.5.5 0 0 0-1 0V10h-1.5a.5.5 0 0 0-.5.5m2.5 1.5a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2a.5.5 0 0 0-.5-.5" />
                </svg>
            </button>
            {{-- Filter email fetch backdating --}}
            <div class="flex justify-end items-center space-x-4 mt-4">
                <button wire:click="connectMailbox" wire:target="connectMailbox"
                    wire:loading.class="opacity-50 cursor-wait" wire:loading.attr="disabled"
                    class="flex items-center gap-1 bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                        <path
                            d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9" />
                        <path fill-rule="evenodd"
                            d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z" />
                    </svg>
                    <span wire:loading.remove>{{ __('sync') }}</span>
                    <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z">
                        </path>
                    </svg>
                    <span wire:loading>{{ __('fetching...') }}</span>

                </button>

                <div class="">
                    <select wire:model.live="filter"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <option value="day">Day</option>
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                    </select>
                </div>
            </div>
        </div>

        <div x-show="open" style="display: none;">
            <h3 class="text-2xl text-gray-500">AI Assistant settings (ollama) </h3>
            {{-- Assistant settings --}}
            <div class="mb-3">
                <label for="model" class="block text-gray-600">Pick a model</label>
                <select name="model" id="model" wire:model="selectedModel"
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

                    @forelse ($models['models'] as $model)
                        <option value="{{ $model['model'] }}"
                            {{ $model['model'] === $selectedModel ? 'selected' : '' }}>
                            {{ $model['name'] }}</option>
                    @empty
                        <option value="" disabled>no models available</option>
                    @endforelse
                </select>
            </div>

            <div class="mb-3">
                <label for="system" class="text-gray-600 mt-4 block">Assistant Configuration</label>
                <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                    wire:model.live="assistantSystem" name="system" id="system" cols="30" rows="10">{{ $assistantSystem }}</textarea>
                {{-- Email connection form --}}
            </div>

            <hr class="my-3">

            <h3 class="text-2xl text-gray-500">Mailbox settings (imap) </h3>
            <div class="mb-3">
                <form wire:submit.prevent="connectMailbox" class="md:grid md:grid-cols-2 md:gap-4">
                    <input
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="text" wire:model="host" placeholder="IMAP Host">
                    <input
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="text" wire:model="port" placeholder="Port">
                    <input
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="text" wire:model="encryption" placeholder="Encryption (ssl/tls)">
                    <input
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="email" wire:model="username" placeholder="Email">
                    <input
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 md:col-span-2"
                        type="password" wire:model="password" placeholder="Password">
                    <button wire:target="connectMailbox" wire:loading.attr="disabled" wire:loading.class="bg-gray-600"
                        wire:loading.class.remove="flex justify-center items-center bg-gray-900"
                        class="w-full bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 md:col-span-2 relative"
                        type="submit">
                        <span wire:loading.remove>{{ __('Sync') }}</span>
                        <div wire:loading class="inet-0">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z"></path>
                            </svg>
                        </div>
                        <span wire:loading>{{ __('Fetching') }}</span>

                    </button>
                </form>
            </div>
        </div>

    </div>
    {{-- /#settings --}}




    <div id="reply-area" class="p-4 bg-white shadow-md rounded-lg lg:max-w-7xl md:max-w-3xl w-full">
        {{-- Reply form --}}
        @if (array_key_exists('message', $reply) && array_key_exists('content', $reply['message']))
            <div class="reply my-4">
                <textarea
                    class="w-full rounded-lg p-3 border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 ease-in-out"
                    name="reply" id="reply" rows="10">{!! $reply['message']['content'] !!}</textarea>
                <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mt-3 focus:outline-none focus:shadow-outline transition duration-200 ease-in-out">Reply</button>
            </div>
        @endif
    </div>
    {{-- /#reply-area --}}

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
    {{-- /#inbox-table --}}

</div>
