<ul>
    @if (!empty($mailboxes))
    @foreach ($mailboxes as $index => $box)

    <li wire:key="folder-{{ $index }}" class="transition-all rounded-lg border-l-4 border-transparent hover:border-gray-800">

        <button wire:click="switchMailboxFolder({{ json_encode($box) }})" type=" button"
            class="flex gap-2 w-full text-start rounded-md p-2 {{ $selectedMailbox == $box['shortpath'] ? 'bg-gray-800 text-white' : '' }}" wire:loading.attr="disabled">

            {{ preg_replace('/^INBOX\.(.*)$/', '$1', $box['shortpath']) }}

            @if($selectedMailbox == $box['shortpath'])
            @include('partials.loader')
            @endif

        </button>
    </li>
    @endforeach
    @endif
</ul>
