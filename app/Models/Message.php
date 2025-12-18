<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'message_identifier',
        'subject',
        'from',
        'sender',
        'reply_to_addresses',
        'date',
        'content',
        'is_seen',
        'is_answered',
        'is_recent',
        'is_flagged',
        'is_deleted',
        'is_draft',
        'mailbox_folder'
    ];



    protected function casts(): array
    {
        return [
            'reply_to_addresses' => 'array'
        ];
    }

    /**
     * Relationship: Message belongs to Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Relationship: Message has many Replies
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }
}
