<button
    popovertarget="message-popover-{{ $index }}"
    class="px-4 py-2 rounded-lg {{$message['content'] ? 'border border-green-600 bg-green-300 dark:bg-green-600/20 dark:text-green-600 text-green-950' : 'bg-gray-200 text-gray-600 dark:bg-slate-700 dark:text-slate-400'}}  hover:bg-dark-950 focus:outline-none focus:shadow-outline">
    <i class="bi bi-arrows-angle-expand"></i>

    <span class="hidden lg:inline">
        Open
    </span>
</button>

<dialog popover id="message-popover-{{ $index }}"
    class="max-w-7xl m-auto bg-white dark:bg-slate-800 dark:text-slate-300 border dark:border-slate-700 p-6 rounded-lg shadow-xl max-h-screen overflow-y-auto">


    <header class="modal-header relative">

        <h3 class="text-slate-800 dark:text-slate-600 text-md uppercase">Message card</h3>

        <button popovertarget="message-popover-{{ $index }}"
            popovertargetaction="hide"
            class=" absolute end-2 top-2 px-4 py-2 rounded bg-gray-200 text-gray-600 hover:bg-gray-100 focus:outline-none focus:shadow-outline">
            <i class="bi bi-x"></i>
        </button>



    </header>
    {{-- /.modal-header --}}

    @if ($fetching == true)
    <div>LOADING MESSAGE...</div>
    @endif

    @if (array_key_exists($message['message_identifier'], $reply))

    <div wire:key="message-{{ now()->timestamp }}" class="mt-4">
        @livewire(
        'ai-reply.reply-form-component',
        [
        'reply' => $reply[$message['message_identifier']],
        'message' => $message,
        ],
        key(now()->timestamp)
        )

        {{-- /Livewire ai-reply.reply-form-component --}}
    </div>
    @endif


    @include('partials.processing-messages')

    @include('partials.message.metadata-received-message')

    @include('partials.message.received-message')

    <div class="text-end my-2">
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
    </div>
    {{-- /Process message button --}}
</dialog>
