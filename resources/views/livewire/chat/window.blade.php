<div class="h-[70vh] max-w-6xl mx-auto flex flex-col bg-white dark:bg-slate-800 rounded-lg shadow">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button wire:click="toggleConversationList" class="text-gray-600 dark:text-slate-300 md:hidden">
                <i class="fas fa-bars"></i>
            </button>
            <i class="bi bi-robot text-2xl text-gray-600 dark:text-slate-300"></i>
            <div>
                <div class="font-semibold text-sm dark:text-slate-100">InboxAI Assistant</div>
                <div class="text-xs text-gray-500 dark:text-slate-400">Conversational email assistant</div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs text-gray-500 dark:text-slate-400">Connected accounts: {{ \App\Models\Account::where('is_active', true)->count() }}</div>
            <button type="button" wire:click="toggleSettingsModal" class="text-xs px-3 py-1 rounded-md border border-gray-200 dark:border-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                <i class="bi bi-gear-wide-connected mr-1"></i>Settings
            </button>
            <button type="button" wire:click="startNewConversation" class="text-xs px-3 py-1 rounded-md border border-gray-200 dark:border-slate-700 dark:text-slate-200">New chat</button>
        </div>
    </div>

    <div class="flex-1 grid grid-cols-12 min-h-0">
        <div class="{{ $showConversationList ? 'col-span-12 md:block md:col-span-4' : 'hidden md:block md:col-span-4' }} border-r border-gray-200 dark:border-slate-700 overflow-auto p-3 space-y-2">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-slate-400 px-1 flex items-center justify-between">
                <span>Conversations</span>
                <button wire:click="toggleConversationList" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 md:hidden">
                    <i class="fas fa-times"></i>
                </button>
            </div>

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

        <div class="{{ $showConversationList ? 'hidden md:flex md:col-span-8' : 'col-span-12 flex md:flex md:col-span-8' }} flex-col min-h-0">
            <div class="flex-1 overflow-auto p-4 pb-24 space-y-4" id="chat-history">
                @if(!$activeConversation)
                    <div class="text-center text-gray-500 dark:text-slate-400 pt-12">
                        Start a new chat by sending a message.
                    </div>
                @else
                    @forelse($messages as $message)
                        <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg px-4 py-3 text-sm border border-gray-200 dark:border-slate-700 {{ $message->role === 'user' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 dark:bg-slate-700 dark:text-slate-100' }} relative group">
                                @if ($message->role === 'user')
                                    @if ($editingMessageId === $message->id)
                                        <form wire:submit.prevent="saveEdit" class="space-y-2">
                                            <textarea
                                                wire:model="editingContent"
                                                class="w-full p-2 border border-gray-300 rounded bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                rows="3"
                                            ></textarea>
                                            <div class="flex space-x-2">
                                                <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                                    <i class="fas fa-check"></i> Save
                                                </button>
                                                <button wire:click="cancelEdit" type="button" class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="whitespace-pre-wrap">{{ $message->content }}</div>
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex space-x-1">
                                            <button wire:click="editMessage({{ $message->id }})" class="text-gray-200 hover:text-white p-1">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button wire:click="deleteMessage({{ $message->id }})" class="text-gray-200 hover:text-white p-1" onclick="confirm('Delete this message?') || event.stopImmediatePropagation()">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <div class="whitespace-pre-wrap">{{ $message->content }}</div>
                                @endif

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

                    @if ($isProcessing)
                        <div class="flex justify-start">
                            <div class="max-w-[80%] rounded-lg px-4 py-3 text-sm border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700 dark:text-slate-100">
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Thinking...</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom Input -->
    <div class="w-full p-4 bg-zinc-900 border-t border-gray-200 dark:border-slate-700" style="position:fixed; bottom: 0; left:0;">
        <form wire:submit.prevent="sendMessage">
            <div class="flex gap-2">
                <textarea
                    wire:model.defer="userInput"
                    rows="2"
                    placeholder="Ask about your emails…"
                    class="flex-1 p-3 border rounded-md dark:bg-slate-900 dark:text-white {{ $isProcessing ? 'opacity-50' : '' }}"
                    {{ $isProcessing ? 'disabled' : '' }}
                ></textarea>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md {{ $isProcessing ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700' }}"
                    {{ $isProcessing ? 'disabled' : '' }}
                >
                    @if($isProcessing)
                        <i class="fas fa-spinner fa-spin"></i>
                    @else
                        <span class=" text-2xl">📨</span>
                    @endif
                </button>
            </div>
        </form>
    </div>


    <!-- Settings Modal -->
    @if($showSettingsModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="toggleSettingsModal">
            <div class="bg-white dark:bg-slate-800 rounded-lg p-6 w-full max-w-2xl max-h-[80vh] overflow-y-auto mb-14" x-on:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold dark:text-slate-100">Chat Settings</h3>
                    <button type="button" wire:click="toggleSettingsModal" class="text-gray-400 hover:text-gray-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <!-- Model Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">AI Model</label>
                    <div class="flex gap-2">
                        <select wire:model="chatModel" class="flex-1 border rounded-md p-2 dark:bg-slate-700 dark:text-white dark:border-slate-600">
                            <option value="">Use default ({{ $this->assistantModel() }})</option>
                            @foreach($availableModels as $model)
                                <option value="{{ $model['name'] }}">{{ $model['name'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" wire:click="loadAvailableModels" class="px-3 py-2 border rounded-md hover:bg-gray-50 dark:hover:bg-slate-700 dark:border-slate-600">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    @if($connectionError)
                        <p class="text-xs text-red-500 mt-1">Unable to connect to Ollama server</p>
                    @endif
                </div>

                <!-- System Prompt -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">System Prompt</label>
                    <textarea wire:model="chatSystemPrompt" rows="6" placeholder="Custom system prompt for this chat..." class="w-full border rounded-md p-3 dark:bg-slate-700 dark:text-white dark:border-slate-600"></textarea>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Leave empty to use the default system prompt</p>
                </div>

                <!-- Available Tools -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Available Tools</label>
                    <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto border rounded-md p-3 dark:border-slate-600">
                        @foreach($this->getTools() as $tool)
                            @php $toolName = $tool['function']['name']; @endphp
                            <label class="flex items-start gap-3 p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-700">
                                <input type="checkbox"
                                    wire:model="enabledTools.{{ $toolName }}"
                                    class="mt-1 rounded border-gray-300 dark:border-slate-600 dark:bg-slate-700">
                                <div class="flex-1">
                                    <div class="font-medium text-sm dark:text-slate-100">{{ $toolName }}</div>
                                    <div class="text-xs text-gray-500 dark:text-slate-400">{{ $tool['function']['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 justify-end">
                    <button type="button" wire:click="toggleSettingsModal" class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-md hover:bg-gray-50 dark:hover:bg-slate-700 dark:text-slate-200">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveChatSettings" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
