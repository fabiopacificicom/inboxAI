<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;
    protected $fillable = [
        'message_id',
        'response_content',
        'confidence_score',
        'message_content'
        // Add any other fields you want to make fillable here
    ];
    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
