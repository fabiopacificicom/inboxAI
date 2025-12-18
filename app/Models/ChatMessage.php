<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tool_calls',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tool_calls' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Relationship: ChatMessage belongs to Conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Check if this message used any tools
     */
    public function usedTools(): bool
    {
        return !empty($this->tool_calls);
    }

    /**
     * Get formatted tool calls for display
     */
    public function getToolCallsDisplay(): ?string
    {
        if (!$this->usedTools()) {
            return null;
        }

        $tools = collect($this->tool_calls)
            ->map(fn($call) => $call['function']['name'] ?? 'Unknown')
            ->join(', ');

        return "🔧 Tools used: {$tools}";
    }
}
