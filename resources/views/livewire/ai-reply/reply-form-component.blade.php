<div id="reply-area" class="p-4 bg-white dark:bg-slate-900 shadow-md rounded-lg" wire:key="{{ now()->timestamp }}">

    @if (session('reply-generated'))
    <div class="text-gray-700 dark:text-slate-400">{{ session('reply-generated') }}</span>
        @endif

        {{-- Reply form --}}
        @if (array_key_exists('message', $reply) && array_key_exists('content', $reply['message']))
        <form wire:submit.prevent="sendReply" wire:key="now()->timestamp">
            <div class=" reply my-4">

                <textarea
                    class="w-full rounded-lg p-3 border border-gray-300 dark:bg-slate-800 dark:text-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 dark:focus:ring-slate-600 transition duration-200 ease-in-out"
                    name="reply" id="reply" rows="10" wire:model.live="content"></textarea>
                @error('content')
                <div class="text-red">{{ $message }}</div>
                @endif
                <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mt-3 focus:outline-none focus:shadow-outline transition duration-200 ease-in-out"
                    type="submit">
                    Reply
                </button>
                @if (session('reply-sent'))
                <span class="text-gray-700 dark:text-slate-400">{{ session('reply-sent') }}</span>
                @endif
            </div>
        </form>
        @endif
    </div>
