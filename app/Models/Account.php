<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_password',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
        ];
    }

    // Encrypt credentials before saving
    public function setImapPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['imap_password'] = Crypt::encryptString($value);
        }
    }

    // Decrypt credentials when accessing
    public function getImapPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    // Encrypt SMTP password
    public function setSmtpPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['smtp_password'] = Crypt::encryptString($value);
        }
    }

    // Decrypt SMTP password
    public function getSmtpPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Relationship: Account belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Account has many Messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}

