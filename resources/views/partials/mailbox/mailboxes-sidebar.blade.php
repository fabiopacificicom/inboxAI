<ul class="overflow-x-auto w-full flex gap-2 flex-wrap lg:block">
    @if (!empty($mailboxes))
    @foreach ($mailboxes as $index => $box)

    <li wire:key="folder-{{ $index }}" class="transition-all rounded-lg border-l-4 border-transparent hover:border-gray-800">

        <button wire:click="switchMailboxFolder({{ json_encode($box) }})" type=" button"
            class="transition-all flex gap-2 w-full text-start text-xs text-slate-800 dark:text-slate-400 dark:hover:text-slate-300 uppercase rounded-md p-2 {{ $selectedMailbox == $box['shortpath'] ? 'bg-gray-800 text-white dark:text-slate-300 text-sm' : '' }}" wire:loading.attr="disabled">

            {{ preg_replace('/^INBOX\.(.*)$/', '$1', $box['shortpath']) }}

            @if($selectedMailbox == $box['shortpath'])
            <i class="bi bi-inboxes"></i>
            @include('partials.loader')
            @endif

        </button>
    </li>
    @endforeach
    @endif
</ul>
