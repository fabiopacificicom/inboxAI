<div id="mailbox-connection-settings" x-data="{ open: false }">
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
