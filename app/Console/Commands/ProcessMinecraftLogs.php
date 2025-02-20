<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Login;
use Carbon\Carbon;

class ProcessMinecraftLogs extends Command
{
    protected $signature = 'minecraft:process-logs';
    protected $description = '处理 Minecraft 服务器日志';

    private $lastLine = 0;
    private $logFile = './logs/latest.log';

    public function handle()
    {
        while (true) {
            $this->processLog();
            sleep(1);
        }
    }

    private function processLog()
    {
        if (!file_exists($this->logFile)) {
            return;
        }

        $lines = file($this->logFile);
        $totalLines = count($lines);

        // 检查是否是新文件
        if ($this->lastLine > $totalLines) {
            $this->lastLine = 0;
        }

        for ($i = $this->lastLine; $i < $totalLines; $i++) {
            $line = $lines[$i];
            
            // 处理登录信息
            if (preg_match('/\[.*?\] \[Server thread\/INFO\]: (.*?) joined the game/', $line, $matches)) {
                $username = $matches[1];
                $this->handleLogin($username);
            }
            
            // 处理登出信息
            if (preg_match('/\[.*?\] \[Server thread\/INFO\]: (.*?) left the game/', $line, $matches)) {
                $username = $matches[1];
                $this->handleLogout($username);
            }
        }

        $this->lastLine = $totalLines;
    }

    private function handleLogin($username)
    {
        $user = User::firstOrCreate(['username' => $username]);
        
        // 检查是否在1分钟内重新登录
        $lastLogin = $user->logins()->latest()->first();
        if ($lastLogin && $lastLogin->created_at->diffInMinutes(now()) <= 1) {
            return;
        }

        $user->update([
            'is_online' => true,
            'last_login_at' => now()
        ]);

        Login::create([
            'user_id' => $user->id
        ]);
    }

    private function handleLogout($username)
    {
        $user = User::where('username', $username)->first();
        if (!$user) return;

        $login = $user->logins()
            ->whereNull('logout_at')
            ->latest()
            ->first();

        if ($login) {
            $duration = now()->diffInSeconds($login->created_at);
            
            // 确保时长合理
            if ($duration > 0 && $duration <= 86400) {
                $login->update([
                    'logout_at' => now(),
                    'duration' => $duration
                ]);

                // 更新用户总在线时间
                $user->update([
                    'is_online' => false,
                    'total_online_time' => $user->total_online_time + $duration
                ]);

                // 更新每日统计
                $this->updateDailyStats($user, $login, $duration);
            } else {
                // 如果时长不合理，只更新状态
                $login->update(['logout_at' => now()]);
                $user->update(['is_online' => false]);
            }
        } else {
            // 如果没有找到未登出的记录，只更新状态
            $user->update(['is_online' => false]);
        }
    }

    private function updateDailyStats($user, $login, $duration)
    {
        $date = $login->created_at->toDateString();
        $dailyStat = $user->dailyStats()->firstOrCreate(['date' => $date]);
        $dailyStat->increment('online_time', $duration);
    }

    protected function findUuidFromLines(array $lines, string $username): ?string
    {
        // 反向遍历日志行，因为UUID信息通常出现在"joined the game"之前
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = $lines[$i];
            
            // 检查是否包含特定玩家的UUID信息
            if (strpos($line, "UUID of player $username is") !== false) {
                // 使用正则表达式提取UUID
                if (preg_match('/UUID of player \w+ is ([0-9a-f-]+)/', $line, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        return null;
    }

    protected function processJoinedGame($line, $lines)
    {
        // 提取用户名
        if (preg_match('/(\w+) joined the game/', $line, $matches)) {
            $username = $matches[1];
            
            // 在玩家加入之前查找UUID
            $uuid = $this->findUuidFromLines($lines, $username);
            
            if ($uuid) {
                // 创建或更新用户记录
                $user = User::firstOrCreate([
                    'minecraft_uuid' => $uuid
                ], [
                    'username' => $username
                ]);
                
                // 处理登录记录等后续逻辑...
            }
        }
    }
} 