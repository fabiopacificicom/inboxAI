<div id="reply-area" class="p-4 bg-white shadow-md rounded-lg">
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
