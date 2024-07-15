<div>



    @teleport('#settings')
    @livewire('ai-reply.ollama-settings', [
        'selectedModel' => $selectedModel,
        'assistantSystem' => $assistantSystem,
        'models' => $models,
        'ollamaServerAddress' => $ollamaServerAddress
    ])
    @endteleport

    @livewire('ai-reply.mailbox-connection-component', [
        'host' => $host,
        'port' => $port,
        'encryption' => $encryption,
        'username' => $username,
        'password' => $password
    ])

    @livewire('ai-reply.message-list-component', [
        'messages' => $messages,
        'selectedModel' => $selectedModel,
        'assistantSystem' => $assistantSystem,
        'ollamaServerAddress' => $ollamaServerAddress
    ])

</div>
