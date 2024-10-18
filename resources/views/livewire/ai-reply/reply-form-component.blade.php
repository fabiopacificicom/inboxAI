<div id="reply-area-{{$message?->id}}" class="p-4 bg-white dark:bg-slate-900 shadow-md rounded-lg" wire:key="{{ now()->timestamp }}">

    @if (session('reply-generated'))
    <span class="text-gray-700 dark:text-slate-400">{{ session('reply-generated') }}</span>
    @endif

    {{-- Reply form --}}

    <form wire:submit.prevent="sendReply" wire:key="now()->timestamp">
        <div class="reply my-4">
            <textarea
                class="w-full rounded-lg p-3 border border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-blue-500 focus:ring focus:ring-slate-800 dark:focus:ring-slate-600 transition duration-200 ease-in-out"
                name="reply" id="reply-{{$message?->id}}" rows="10" wire:model="content"></textarea>
            @error('content')
            <div class="text-red">{{ $message }}</div>
            @endif
            <button
                class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded-lg mt-3 focus:outline-none focus:shadow-outline transition duration-200 ease-in-out"
                type="submit">
                Send Reply
            </button>
            @if (session('reply-sent'))
            <span class="text-gray-700 dark:text-slate-400">{{ session('reply-sent') }}</span>
            @endif
        </div>
    </form>
</div>