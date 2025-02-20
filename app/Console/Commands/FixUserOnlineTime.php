<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Login;
use Carbon\Carbon;

class FixUserOnlineTime extends Command
{
    protected $signature = 'minecraft:fix-online-time';
    protected $description = '修复用户在线时间和在线状态';

    public function handle()
    {
        $this->info('开始修复用户数据...');

        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                // 修复在线状态
                $hasActiveLogin = $user->logins()
                    ->whereNull('logout_at')
                    ->exists();

                // 修复总在线时间
                $totalTime = $user->logins()
                    ->whereNotNull('logout_at')
                    ->whereNotNull('duration')
                    ->where('duration', '>', 0)
                    ->where('duration', '<=', 86400)
                    ->sum('duration');

                // 如果有未登出的记录，加上当前时间到最后登录时间的差值
                if ($hasActiveLogin) {
                    $lastLogin = $user->logins()
                        ->whereNull('logout_at')
                        ->latest()
                        ->first();
                    
                    if ($lastLogin) {
                        $additionalTime = Carbon::now()->diffInSeconds($lastLogin->created_at);
                        if ($additionalTime > 0 && $additionalTime <= 86400) {
                            $totalTime += $additionalTime;
                        }
                    }
                }

                $user->update([
                    'is_online' => $hasActiveLogin,
                    'total_online_time' => max(0, $totalTime)
                ]);

                $this->info(sprintf(
                    "修复用户 %s: 在线状态=%s, 总在线时间=%d秒",
                    $user->username,
                    $hasActiveLogin ? '是' : '否',
                    $totalTime
                ));
            }
        });

        $this->info('修复完成');
    }
} 