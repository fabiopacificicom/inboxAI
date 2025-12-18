<div class="mt-8 p-4 bg-slate-900 rounded-md max-h-72 overflow-y-scroll">
    Available Replies
    <ul class="my-3">

        @if ($message)
        @forelse ($message?->replies as $reply)
        <li wire:key="{{now()->timestamp}}" class="text-slate-400 bg-slate-800 p-4 hover:cursor-pointer mb-3">
            <button type="button" wire:click="$dispatch('set-reply-content', { id: '{{$reply->id}}' })">
                {{$reply->response_content}}
            </button>
        </li>
        @empty
        <li>Process the message to generate your first reply</li>
        @endforelse
        @endif
    </ul>
</div>