<div>

    {{-- create a button group --}}
    <div class="flex items-center gap-1">
        {{-- Syncronize mailbox (downloads messages) --}}
        <button id="sync" wire:click="connectMailbox" wire:target="connectMailbox"
            wire:loading.class="opacity-50 cursor-wait" wire:loading.attr="disabled"
            class="flex items-center gap-1 p-3 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">



            <span wire:loading.remove class="flex gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-envelope-arrow-down-fill" viewBox="0 0 16 16">
                    <path
                        d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zm.192 8.159 6.57-4.027L8 9.586l1.239-.757.367.225A4.49 4.49 0 0 0 8 12.5c0 .526.09 1.03.256 1.5H2a2 2 0 0 1-1.808-1.144M16 4.697v4.974A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-1.965.45l-.338-.207z" />
                    <path
                        d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-1.646a.5.5 0 0 1-.722-.016l-1.149-1.25a.5.5 0 1 1 .737-.676l.28.305V11a.5.5 0 0 1 1 0v1.793l.396-.397a.5.5 0 0 1 .708.708z" />
                </svg>
                <span class="text-xs">{{ __('Download messages') }}</span>

            </span>

            <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z">
                </path>
            </svg>
            <span wire:loading class="text-xs">{{ __('Downloading...') }}</span>

        </button>
        {{-- Mailbox settings --}}
        <button title="mailbox settings"
            class="flex items-center gap-1 p-3 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-5"
            popovertarget="mailbox-settings" popovertargetaction="show">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-mailbox" viewBox="0 0 16 16">
                <path
                    d="M4 4a3 3 0 0 0-3 3v6h6V7a3 3 0 0 0-3-3m0-1h8a4 4 0 0 1 4 4v6a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V7a4 4 0 0 1 4-4m2.646 1A4 4 0 0 1 8 7v6h7V7a3 3 0 0 0-3-3z" />
                <path
                    d="M11.793 8.5H9v-1h5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.354-.146zM5 7c0 .552-.448 0-1 0s-1 .552-1 0a1 1 0 0 1 2 0" />
            </svg>
            <span class="text-xs">Settings</span>
        </button>

    </div>

    <div id="mailbox-settings" popover class="bg-white shadow rounded w-full max-w-7xl mx-auto p-6">

        {{-- Mailbox settings --}}

        <h3 class="text-2xl text-gray-500">Mailbox settings (imap) </h3>
        <p class="mb-3">Here you can configure your imap mailbox settings.</p>
        <div class="mb-3">
            <form id="connection" wire:submit.prevent="connectMailbox"
                class="md:grid md:grid-cols-3 md:gap-4 gap-2 mb-3">

                <div>
                    <label for="host" class="block text-sm font-medium text-gray-700">Host:</label>

                    <input id="host"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="text" wire:model.blur="host" placeholder="IMAP Host">

                </div>

                <div>

                    <label for="port" class="block text-sm font-medium text-gray-700">Port:</label>
                    <input id="port"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="text" wire:model.blur="port" placeholder="Port">
                </div>

                <div>
                    <label for="encryption" class="block text-sm font-medium text-gray-700">Encryption:</label>
                    <input id="encryption"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="text" wire:model.blur="encryption" placeholder="Encryption (ssl/tls)">
                </div>


                <div>

                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address:</label>
                    <input id="email"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
                        type="email" wire:model.blur="username" placeholder="Email">

                </div>

                <div>

                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                    <input
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 md:col-span-2"
                        type="password" wire:model.blur="password" placeholder="Password">

                </div>


            </form>
            <button form="connection" wire:target="connectMailbox" wire:loading.attr="disabled"
                wire:loading.class="bg-gray-600"
                wire:loading.class.remove="flex justify-center items-center bg-gray-900"
                class="w-full bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 md:col-span-2 relative"
                type="submit">
                <span wire:loading.remove>{{ __('Sync') }}</span>
                <div wire:loading class="inet-0">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 01.33-2.217l1.745 1.036A6 6 0 006 12h-2z"></path>
                    </svg>
                </div>
                <span wire:loading>{{ __('Fetching') }}</span>

            </button>

        </div>

    </div>
</div>
