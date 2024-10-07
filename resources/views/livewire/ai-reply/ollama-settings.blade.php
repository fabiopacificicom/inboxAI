<div>
    {{-- Assistant settings --}}
    <h3 class="flex items-center justify-between text-2xl text-gray-500 mt-4 mb-6">
        AI Server Settings

        <button type="button" popovertargetaction="hide" popovertarget="settings" class="ml-auto">
            <i class="bi bi-x"></i>
        </button>
    </h3>

    {{-- server address --}}
    <div class="mb-3">
        <label for="ollamaServerAddress" class="block text-gray-600">Ollama Server Address <span
                class="w-4 h-4 rounded-full inline-block {{ !$connectionError ? 'bg-green-500' : 'bg-red-500' }}"></span>
        </label>

        <input type="text" wire:model.blur="ollamaServerAddress" name="ollamaServerAddress" id="ollamaServerAddress" class="w-full p-2 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">

        @if ($connectionError)
        <div class="text-sm text-red-500">{{ $connectionError }}</div>
        @endif

        @error('ollamaServerAddress')
        <span class="error">{{ $message }}</span>
        @enderror

    </div>
    {{-- /server address --}}


    <h4 class="flex items-center justify-between text-xl text-gray-500 mt-4 mb-6">
        Models

        <button type="button" wire:click="refreshModels()" class="text-sm">
            <i class="bi bi-arrow-clockwise"></i>

            Refresh Models
            <div x-data="{show: false}" x-on:models-updated="show = !show; setTimeOut(()=> show = false, 2000)" x-bind:class="{ 'hidden': !show }">Completed</div>

        </button>
    </h4>


    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">

        {{-- models --}}
        <div class="mb-3">
            <label for="assistant-model" class="block text-gray-600">Assistant Model</label>
            <select name="assistant-model" id="assistant-model" wire:model.live="selectedModel"
                class="w-full p-2 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">


                @if (is_array($models) && array_key_exists('models', $models))
                @forelse ($models['models'] as $model)
                <option value="{{ $model['model'] }}"
                    {{ $model['model'] === $selectedModel ? 'selected' : '' }}>
                    {{ $model['name'] }}
                </option>
                @empty
                <option value="" disabled>no models available</option>
                @endforelse
                @endif
            </select>
        </div>

        <div class="mb-3">
            <label for="classifier-model" class="block text-gray-600">Classifier model</label>
            <select name="classifier-model" id="classifier-model" wire:model.live="selectedClassifier"
                class="w-full p-2 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">
                @if (is_array($models) && array_key_exists('models', $models))

                @forelse ($models['models'] as $model)
                <option value="{{ $model['model'] }}"
                    {{ $model['model'] === $selectedClassifier ? 'selected' : '' }}>
                    {{ $model['name'] }}
                </option>
                @empty
                <option value="" disabled>no models available</option>
                @endforelse
                @endif
            </select>
        </div>
        {{-- /models --}}

    </div>


    {{-- system prompts --}}
    <div class="mb-3">
        <label for="classifierSystem" class="text-gray-600 mt-4 block">Classifier System</label>
        <textarea class="w-full p-2 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
            wire:model.live.delay3s="classifierSystem" name="classifierSystem" id="classifierSystem" cols="30"
            rows="10"></textarea>
        @error('classifierSystem')
        <span class="error">{{ $message }}</span>
        @enderror

    </div>


    <div class="mb-3">
        <label for="assistantSystem" class="text-gray-600 mt-4 block">Assistant System</label>
        <textarea class="w-full p-2 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200"
            wire:model.live.delay3s="assistantSystem" name="assistantSystem" id="assistantSystem" cols="30" rows="10"></textarea>
        @error('assistantSystem')
        <span class="error">{{ $message }}</span>
        @enderror

    </div>

    {{-- /system prompts --}}




</div>
