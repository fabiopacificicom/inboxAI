<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    /**
     * Relationship: Conversation belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Conversation has many ChatMessages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    /**
     * Get the latest message in the conversation
     */
    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    /**
     * Auto-generate title from first user message if not set
     */
    public function generateTitle(): void
    {
        if ($this->title) {
            return;
        }

        $firstUserMessage = $this->messages()
            ->where('role', 'user')
            ->first();

        if ($firstUserMessage) {
            // Use first 50 chars of first message as title
            $this->title = \Str::limit($firstUserMessage->content, 50);
            $this->save();
        }
    }
}
