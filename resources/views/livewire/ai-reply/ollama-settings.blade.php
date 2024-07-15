<div>
    {{-- Assistant settings --}}
    <h3 class="text-2xl text-gray-500 mt-4 mb-3">AI Assistant settings (ollama) </h3>

    <div class="mb-3">
        <label for="ollamaServerAddress" class="block text-gray-600">Ollama Server Address <span class="w-4 h-4 rounded-full inline-block {{!$connectionError ? 'bg-green-500' : 'bg-red-500' }}"></span> </label>
        <input type="text" wire:model.blur="ollamaServerAddress" name="ollamaServerAddress" id="ollamaServerAddress" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

        @if ($connectionError)
        <div class="text-sm text-red-500">{{$connectionError}}</div>
        @endif
    </div>

    <div class="mb-3">
        <label for="model" class="block text-gray-600">Pick a model</label>
        <select name="model" id="model" wire:model="selectedModel"
            class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

            @forelse ($models['models'] as $model)
                <option value="{{ $model['model'] }}"
                    {{ $model['model'] === $selectedModel ? 'selected' : '' }}>
                    {{ $model['name'] }}</option>
            @empty
                <option value="" disabled>no models available</option>
            @endforelse
        </select>
    </div>

    <div class="mb-3">
        <label for="system" class="text-gray-600 mt-4 block">Assistant Configuration</label>
        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
            wire:model.live="assistantSystem" name="system" id="system" cols="30" rows="10">{{ $assistantSystem }}</textarea>
        {{-- Email connection form --}}
    </div>
</div>
