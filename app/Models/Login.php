<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $fillable = [
        'user_id',
        'login_at',
        'logout_at',
        'duration',
        'created_at'
    ];

    protected $casts = [
        'logout_at' => 'datetime',
        'duration' => 'integer',
        'login_at' => 'datetime',
    ];

    /**
     * 需要被转换成日期的属性
     *
     * @var array
     */
    protected $dates = [
        'login_at',
        'logout_at',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 