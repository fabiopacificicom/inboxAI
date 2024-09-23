<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
