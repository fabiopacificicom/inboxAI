<div class="h-[70vh] max-w-6xl mx-auto flex flex-col bg-white dark:bg-slate-800 rounded-lg shadow">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="bi bi-robot text-2xl text-gray-600 dark:text-slate-300"></i>
            <div>
                <div class="font-semibold text-sm dark:text-slate-100">InboxAI Assistant</div>
                <div class="text-xs text-gray-500 dark:text-slate-400">Conversational email assistant</div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs text-gray-500 dark:text-slate-400">Connected accounts: {{ \App\Models\Account::where('is_active', true)->count() }}</div>
            <button type="button" wire:click="startNewConversation" class="text-xs px-3 py-1 rounded-md border border-gray-200 dark:border-slate-700 dark:text-slate-200">New chat</button>
        </div>
    </div>

    <div class="flex-1 grid grid-cols-12 min-h-0">
        <div class="col-span-4 border-r border-gray-200 dark:border-slate-700 overflow-auto p-3 space-y-2">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-slate-400 px-1">Conversations</div>

            @forelse($conversations as $conversation)
                <button
                    type="button"
                    wire:click="selectConversation({{ $conversation->id }})"
                    class="w-full text-left rounded-md px-3 py-2 border border-transparent hover:border-gray-200 dark:hover:border-slate-600 {{ ($activeConversation?->id === $conversation->id) ? 'bg-gray-100 dark:bg-slate-700' : 'bg-transparent' }}"
                >
                    <div class="text-sm font-semibold dark:text-slate-100">
                        {{ $conversation->title ?? ('Conversation #' . $conversation->id) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">
                        {{ $conversation->latestMessage?->created_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                    </div>
                </button>
            @empty
                <div class="text-xs text-gray-500 dark:text-slate-400 px-1">No conversations yet.</div>
            @endforelse
        </div>

        <div class="col-span-8 flex flex-col min-h-0">
            <div class="flex-1 overflow-auto p-4 space-y-4" id="chat-history">
                @if(!$activeConversation)
                    <div class="text-center text-gray-500 dark:text-slate-400 pt-12">
                        Start a new chat by sending a message.
                    </div>
                @else
                    @forelse($messages as $message)
                        <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg px-4 py-3 text-sm border border-gray-200 dark:border-slate-700 {{ $message->role === 'user' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 dark:bg-slate-700 dark:text-slate-100' }}">
                                <div class="whitespace-pre-wrap">{{ $message->content }}</div>

                                @if($message->role === 'assistant' && $message->usedTools())
                                    <div class="mt-2 text-xs opacity-80">
                                        {{ $message->getToolCallsDisplay() }}
                                    </div>

                                    @php
                                        $toolResults = $message->metadata['tool_results'] ?? null;
                                    @endphp

                                    @if($toolResults)
                                        <pre class="mt-2 text-[11px] whitespace-pre-wrap bg-white/60 dark:bg-slate-900/50 rounded-md p-2 border border-gray-200 dark:border-slate-600">{{ json_encode($toolResults, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 dark:text-slate-400 pt-12">
                            No messages yet.
                        </div>
                    @endforelse
                @endif
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-slate-700">
                <form wire:submit.prevent="sendMessage">
                    <div class="flex gap-2">
                        <textarea wire:model.defer="userInput" rows="2" placeholder="Ask about your emails…" class="flex-1 p-3 border rounded-md dark:bg-slate-900 dark:text-white" ></textarea>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
