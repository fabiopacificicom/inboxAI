<div class="mt-8 p-4 bg-slate-900 rounded-md">
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