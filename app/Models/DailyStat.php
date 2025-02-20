<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'online_time'
    ];

    protected $casts = [
        'date' => 'date',
        'online_time' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 