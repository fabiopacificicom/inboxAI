<div>


    <div class="mb-4 flex items-end justify-between">

        <button class="flex items-center gap-2 bg-gray-900 text-white p-3 rounded-2xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50" popovertarget="settings" popoveraction="show">
            {{ __('Settings') }}
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-sliders2-vertical" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M0 10.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H3V1.5a.5.5 0 0 0-1 0V10H.5a.5.5 0 0 0-.5.5M2.5 12a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2a.5.5 0 0 0-.5-.5m3-6.5A.5.5 0 0 0 6 6h1.5v8.5a.5.5 0 0 0 1 0V6H10a.5.5 0 0 0 0-1H6a.5.5 0 0 0-.5.5M8 1a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2A.5.5 0 0 0 8 1m3 9.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H14V1.5a.5.5 0 0 0-1 0V10h-1.5a.5.5 0 0 0-.5.5m2.5 1.5a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 1 0v-2a.5.5 0 0 0-.5-.5" />
            </svg>
        </button>

        <dialog id="settings" popover>
            @livewire('ai-reply.ollama-settings', [
                'selectedModel' => $selectedModel,
                'selectedClassifier' => $selectedClassifier,
                'assistantSystem' => $assistantSystem,
                'classifierSystem' => $classifierSystem,
                'models' => $models,
                'ollamaServerAddress' => $ollamaServerAddress,
            ])
        </dialog>



        @livewire('ai-reply.mailbox-connection-component')



    </div>


    @livewire('ai-reply.message-list-component', [
        'selectedModel' => $selectedModel,
        'assistantSystem' => $assistantSystem,
        'ollamaServerAddress' => $ollamaServerAddress,
    ])

</div>
