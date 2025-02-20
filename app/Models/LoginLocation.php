<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLocation extends Model
{
    protected $fillable = [
        'login_id',
        'user_id',
        'world',
        'x',
        'y',
        'z',
        'entity_id',
        'ip',
    ];

    /**
     * 获取关联的登录记录
     */
    public function login()
    {
        return $this->belongsTo(Login::class);
    }

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取格式化的坐标
     */
    public function getFormattedCoordinatesAttribute()
    {
        return "({$this->x}, {$this->y}, {$this->z})";
    }
} 