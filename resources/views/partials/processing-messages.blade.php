<div class="fixed top-10 right-8 max-w-60 bg-slate-100 shadow-md p-4">
    <h4 class="text-xl">Processes Monitor</h4>
    @forelse ($processingMessages as $processingMessage)
        <div class="text-green-600 p-2" role="alert">
            @foreach ($processingMessage as $icon => $item)
                <span>{{ $icon }}</span>
                <span>
                    {{ $item }}
                </span>
            @endforeach
        </div>
    @empty
        <div>on hold</div>
    @endforelse
    {{-- Processing messages --}}
</div>
