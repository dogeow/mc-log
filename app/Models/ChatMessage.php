<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'content',
        'channel',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    /**
     * 获取发送消息的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 