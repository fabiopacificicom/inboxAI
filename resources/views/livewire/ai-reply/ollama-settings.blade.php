<div>
    {{-- Assistant settings --}}
    <h3 class="text-2xl text-gray-500 mt-4 mb-3">AI Assistant settings (ollama) </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
        {{-- server address --}}
        <div class="mb-3">
            <label for="ollamaServerAddress" class="block text-gray-600">Ollama Server Address <span
                    class="w-4 h-4 rounded-full inline-block {{ !$connectionError ? 'bg-green-500' : 'bg-red-500' }}"></span>
            </label>
            <input type="text" wire:model.blur="ollamaServerAddress" name="ollamaServerAddress" id="ollamaServerAddress"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

            @if ($connectionError)
                <div class="text-sm text-red-500">{{ $connectionError }}</div>
            @endif

            @error('ollamaServerAddress')
                <span class="error">{{ $message }}</span>
            @enderror

        </div>
        {{-- /server address --}}

        {{-- models --}}
        <div class="mb-3">
            <label for="assistant-model" class="block text-gray-600">Assistant Model</label>
            <select name="assistant-model" id="assistant-model" wire:model.live="selectedModel"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

                @forelse ($models['models'] as $model)
                    <option value="{{ $model['model'] }}" {{ $model['model'] === $selectedModel ? 'selected' : '' }}>
                        {{ $model['name'] }}
                    </option>
                @empty
                    <option value="" disabled>no models available</option>
                @endforelse
            </select>
        </div>

        <div class="mb-3">
            <label for="classifier-model" class="block text-gray-600">Classifier model</label>
            <select name="classifier-model" id="classifier-model" wire:model.live="selectedClassifier"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

                @forelse ($models['models'] as $model)
                    <option value="{{ $model['model'] }}" {{ $model['model'] === $selectedClassifier ? 'selected' : '' }}>
                        {{ $model['name'] }}
                    </option>
                @empty
                    <option value="" disabled>no models available</option>
                @endforelse
            </select>
        </div>
        {{-- /models --}}
    </div>


    {{-- system prompts --}}
    <div class="mb-3">
        <label for="classifierSystem" class="text-gray-600 mt-4 block">Classifier System</label>
        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
            wire:model.live.delay3s="classifierSystem" name="classifierSystem" id="classifierSystem" cols="30"
            rows="10"></textarea>
        @error('classifierSystem')
            <span class="error">{{ $message }}</span>
        @enderror

    </div>


    <div class="mb-3">
        <label for="assistantSystem" class="text-gray-600 mt-4 block">Assistant System</label>
        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
            wire:model.live.delay3s="assistantSystem" name="assistantSystem" id="assistantSystem" cols="30" rows="10"></textarea>
        @error('assistantSystem')
            <span class="error">{{ $message }}</span>
        @enderror

    </div>

    {{-- /system prompts --}}




</div>
