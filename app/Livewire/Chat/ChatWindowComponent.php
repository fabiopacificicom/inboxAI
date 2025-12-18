<?php

namespace App\Livewire\Chat;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Traits\HandleAiResponse;
use App\Traits\HasToolAccess;
use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ChatWindowComponent extends Component
{
    use HandleAiResponse;
    use HasToolAccess;

    public ?int $activeConversationId = null;
    public string $userInput = '';
    public string $chatModel = '';
    public string $chatSystemPrompt = '';
    public array $enabledTools = [];
    public bool $showSettingsModal = false;
    public array $availableModels = [];
    public bool $connectionError = false;
    public bool $isProcessing = false;
    public bool $showConversationList = false;
    public ?int $editingMessageId = null;
    public string $editingContent = '';

    public function mount(): void
    {
        $this->activeConversationId = Conversation::query()
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->value('id');

        $this->loadChatSettings();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->activeConversationId = $conversationId;
    }

    public function startNewConversation(): void
    {
        $this->activeConversationId = null;
        $this->userInput = '';
        $this->editingMessageId = null;
        $this->editingContent = '';
    }

    public function toggleConversationList(): void
    {
        $this->showConversationList = !$this->showConversationList;
    }

    public function toggleSettingsModal(): void
    {
        $this->showSettingsModal = !$this->showSettingsModal;
        if ($this->showSettingsModal) {
            $this->loadAvailableModels();
        }
    }

    public function saveChatSettings(): void
    {
        Setting::updateOrCreate(['key' => 'chatModel'], ['value' => $this->chatModel]);
        // Keep global assistant model in sync with chat model selection
        if (!empty($this->chatModel)) {
            Setting::updateOrCreate(['key' => 'selectedModel'], ['value' => $this->chatModel]);
        }
        Setting::updateOrCreate(['key' => 'chatSystemPrompt'], ['value' => $this->chatSystemPrompt]);
        Setting::updateOrCreate(['key' => 'enabledTools'], ['value' => json_encode($this->enabledTools)]);

        $this->showSettingsModal = false;
        session()->flash('message', 'Chat settings saved successfully!');
    }

    public function updatedChatModel(string $value): void
    {
        // Persist model selection immediately so it survives full page refreshes
        Setting::updateOrCreate(['key' => 'chatModel'], ['value' => $value]);
        // Also update the shared selectedModel used by other assistants/tools
        if (!empty($value)) {
            Setting::updateOrCreate(['key' => 'selectedModel'], ['value' => $value]);
        }
    }

    public function loadChatSettings(): void
    {
        $this->chatModel = Setting::where('key', 'chatModel')->first()?->value
            ?? $this->assistantModel();

        $this->chatSystemPrompt = Setting::where('key', 'chatSystemPrompt')->first()?->value
            ?? $this->systemPrompt();

        $enabledToolsSetting = Setting::where('key', 'enabledTools')->first()?->value;
        $this->enabledTools = $enabledToolsSetting ? json_decode($enabledToolsSetting, true) : $this->getDefaultEnabledTools();
    }

    public function loadAvailableModels(): void
    {
        try {
            $serverAddress = Setting::where('key', 'ollamaServerAddress')->first()?->value
                ?? config('responder.assistant.server');

            $response = Http::timeout(5000)
                ->withHeader('x-access-token', config('responder.assistant.server_api_token'))
                ->get($serverAddress . config('responder.assistant.tags'));

            $this->availableModels = $response->json()['models'] ?? [];
            $this->connectionError = false;
        } catch (\Throwable $e) {
            $this->connectionError = true;
            Log::error('Failed to load available models', ['error' => $e->getMessage()]);
            $this->availableModels = [];
        }
    }

    public function getDefaultEnabledTools(): array
    {
        return collect($this->getTools())
            ->pluck('function.name')
            ->mapWithKeys(fn($tool) => [$tool => true])
            ->toArray();
    }

    public function editMessage(int $messageId): void
    {
        $message = ChatMessage::query()
            ->whereHas('conversation', fn($q) => $q->where('user_id', Auth::id()))
            ->where('id', $messageId)
            ->where('role', 'user')
            ->first();

        if ($message) {
            $this->editingMessageId = $messageId;
            $this->editingContent = $message->content;
        }
    }

    public function cancelEdit(): void
    {
        $this->editingMessageId = null;
        $this->editingContent = '';
    }

    public function saveEdit(): void
    {
        if (!$this->editingMessageId) {
            return;
        }

        $message = ChatMessage::query()
            ->whereHas('conversation', fn($q) => $q->where('user_id', Auth::id()))
            ->where('id', $this->editingMessageId)
            ->where('role', 'user')
            ->first();

        if ($message) {
            $message->update(['content' => trim($this->editingContent)]);
            $this->editingMessageId = null;
            $this->editingContent = '';
        }
    }

    public function deleteMessage(int $messageId): void
    {
        $message = ChatMessage::query()
            ->whereHas('conversation', fn($q) => $q->where('user_id', Auth::id()))
            ->where('id', $messageId)
            ->first();

        if ($message) {
            // If deleting a user message, also delete the next assistant message if it exists
            if ($message->role === 'user') {
                $nextAssistant = ChatMessage::query()
                    ->where('conversation_id', $message->conversation_id)
                    ->where('role', 'assistant')
                    ->where('id', '>', $messageId)
                    ->orderBy('id')
                    ->first();

                if ($nextAssistant) {
                    $nextAssistant->delete();
                }
            }

            $message->delete();
        }
    }

    public function sendMessage(): void
    {
        $content = trim($this->userInput);
        if ($content === '' || $this->isProcessing) {
            return;
        }

        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $this->isProcessing = true;
        $conversation = $this->resolveConversation($userId);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $content,
        ]);

        $this->userInput = '';

        try {
            [$assistantText, $toolCalls, $toolResults] = $this->generateAssistantReply($conversation);
        } catch (\Throwable $e) {
            Log::error('❌ Chat sendMessage failed', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
            ]);

            $assistantText = 'Sorry — I ran into an error while generating a response. Try again in a moment.';
            $toolCalls = [];
            $toolResults = [];
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $assistantText,
            'tool_calls' => empty($toolCalls) ? null : $toolCalls,
            'metadata' => empty($toolResults) ? null : ['tool_results' => $toolResults],
        ]);

        $conversation->generateTitle();
        $conversation->touch();
        $this->activeConversationId = $conversation->id;
        $this->isProcessing = false;
    }

    private function resolveConversation(int $userId): Conversation
    {
        if ($this->activeConversationId) {
            $existing = Conversation::query()
                ->where('id', $this->activeConversationId)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return Conversation::create([
            'user_id' => $userId,
            'title' => null,
            'context' => [],
        ]);
    }

    /**
     * @return array{0:string,1:array,2:array}
     */
    private function generateAssistantReply(Conversation $conversation): array
    {
        $history = $conversation->messages()
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        $messages = collect([
            ['role' => 'system', 'content' => $this->systemPrompt()],
        ])->merge(
            $history->map(fn (ChatMessage $m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
        )->all();

        $payload = [
            'model' => $this->chatModel ?: $this->assistantModel(),
            'stream' => false,
            'messages' => $messages,
            'tools' => $this->getEnabledTools(),
        ];

        $resp = $this->getResponse($payload);

        $assistantText = (string) data_get($resp, 'message.content', '');
        $toolCalls = data_get($resp, 'message.tool_calls', []);
        if (!is_array($toolCalls)) {
            $toolCalls = [];
        }

        $toolResults = [];

        if (!empty($toolCalls)) {
            foreach ($toolCalls as $call) {
                $toolName = (string) data_get($call, 'function.name', '');
                $arguments = data_get($call, 'function.arguments', []);
                if ($toolName === '' || !($this->enabledTools[$toolName] ?? true)) {
                    continue;
                }

                if (is_string($arguments)) {
                    $decoded = json_decode($arguments, true);
                    $arguments = is_array($decoded) ? $decoded : [];
                } elseif (!is_array($arguments)) {
                    $arguments = [];
                }

                $result = $this->executeToolCall($toolName, $arguments);
                $toolResults[] = [
                    'name' => $toolName,
                    'arguments' => $arguments,
                    'result' => $result,
                ];
            }

            $followUp = [
                'model' => $this->chatModel ?: $this->assistantModel(),
                'stream' => false,
                'messages' => array_merge(
                    [
                        ['role' => 'system', 'content' => $this->toolFollowupPrompt()],
                    ],
                    Arr::where($messages, fn ($m) => ($m['role'] ?? null) !== 'system' || ($m['content'] ?? '') !== $this->systemPrompt()),
                    [
                        ['role' => 'system', 'content' => 'Tool results (JSON): ' . json_encode($toolResults)],
                    ],
                ),
            ];

            $resp2 = $this->getResponse($followUp);
            $assistantText = (string) data_get($resp2, 'message.content', $assistantText);
        }

        $assistantText = trim($assistantText);
        if ($assistantText === '') {
            $assistantText = 'I could not generate a response. Try rephrasing your question.';
        }

        return [$assistantText, $toolCalls, $toolResults];
    }

    private function systemPrompt(): string
    {
        if (!empty($this->chatSystemPrompt)) {
            return $this->chatSystemPrompt;
        }

        $base = Setting::where('key', 'assistantSystem')->first()?->value
            ?? config('responder.assistant.system');

        return implode("\n\n", [
            trim((string) $base),
            "You are InboxAI, an intelligent email assistant managing multiple email accounts for the user.",
            "You have access to tools for:\n- Searching emails across accounts\n- Finding critical/urgent messages\n- Generating summaries\n- Drafting replies\n- Archiving messages\n- Account stats\n- Finding messages by sender",
            "When the user asks about their emails:\n1) Determine which tool(s) to use\n2) Call tools with appropriate parameters\n3) Present results conversationally\n4) Offer follow-up actions",
            "Always specify which account you're referencing when results come from multiple sources.",
            "Never invent email contents or senders. If you need data, call tools.",
        ]);
    }

    private function assistantModel(): string
    {
        return Setting::where('key', 'selectedModel')->first()?->value
            ?? config('responder.assistant.model');
    }

    private function getEnabledTools(): array
    {
        $allTools = $this->getTools();

        return array_filter($allTools, function($tool) {
            $toolName = $tool['function']['name'] ?? null;
            return $toolName && ($this->enabledTools[$toolName] ?? true);
        });
    }

    private function toolFollowupPrompt(): string
    {
        return implode("\n", [
            'You are InboxAI. You have tool results available as JSON in the next system message.',
            'Use them to answer the user clearly. If results are empty, ask a brief clarifying question.',
        ]);
    }

    public function render()
    {
        $userId = Auth::id();

        $conversations = Conversation::query()
            ->where('user_id', $userId)
            ->with('latestMessage')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        $activeConversation = null;
        if ($this->activeConversationId) {
            $activeConversation = Conversation::query()
                ->where('id', $this->activeConversationId)
                ->where('user_id', $userId)
                ->first();
        }

        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
            $this->activeConversationId = $activeConversation->id;
        }

        $messages = $activeConversation
            ? $activeConversation->messages()->get()
            : collect();

        return view('livewire.chat.window', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
        ]);
    }
}
