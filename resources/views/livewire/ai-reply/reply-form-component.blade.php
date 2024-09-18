<div id="reply-area" class="p-4 bg-white shadow-md rounded-lg" wire:key="{{ $message->id }}">
    {{--     <button wire:click="replyMessage()" class="bg-gray-800 text-white p-1 rounded">Generate Reply</button>
 --}}
    @if (session('reply-generated'))
        <div class="text-gray-700">{{ session('reply-generated') }}</span>
    @endif
    @if (session()->has('message'))
        <div class="alert bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            {{ session('message') }}
        </div>
    @endif
    {{-- Reply form --}}
    @if (array_key_exists('message', $reply) && array_key_exists('content', $reply['message']))
        <form wire:submit.prevent="sendReply">
            <div class="reply my-4">
                <textarea
                    class="w-full rounded-lg p-3 border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 ease-in-out"
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
            <span class="text-gray-700">{{ session('reply-sent') }}</span>
        @endif
    </div>
    </form>
    @endif
    </div>
