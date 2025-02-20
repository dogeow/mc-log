<?php

namespace App\Services;

use App\Models\User;
use App\Models\Login;
use App\Models\DailyStat;
use App\Models\ChatMessage;
use App\Models\LoginLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class MinecraftLogService
{
    private $uuidCache = [];
    private $currentLogin = [];

    // 添加恶意用户匹配规则
    public function isMaliciousUser($username)
    {
        return preg_match('/^Cornbread\d+$/', $username);
    }

    public function handleLogin($username, $uuid, $timestamp, $logPath = null, $currentFile = null)
    {
        // 检查是否是恶意用户
        if ($this->isMaliciousUser($username)) {
            return null;
        }

        $user = User::firstOrCreate(['username' => $username, 'uuid' => $uuid]);
        
        // 如果用户已经在线，先处理之前的登录
        if (isset($this->currentLogin[$username])) {
            $this->handleLogout($username, $timestamp);
        }

        // 检查是否在1分钟内重新登录
        $lastLogin = $user->logins()->latest()->first();
        if ($lastLogin && $lastLogin->created_at->diffInMinutes($timestamp) <= 1) {
            return null;
        }

        $this->currentLogin[$username] = $timestamp;

        $login = Login::create([
            'user_id' => $user->id,
            'login_at' => $timestamp,
            'created_at' => $timestamp
        ]);

        // 如果提供了日志路径和文件名，尝试获取登录位置
        if ($logPath && $currentFile) {
            $this->processLoginLocation($login, $user, $username, $logPath, $currentFile);
        }

        $user->update([
            'is_online' => true,
            'last_login_at' => $timestamp
        ]);

        return $login;
    }

    public function handleLogout($username, $timestamp)
    {
        // 检查是否是恶意用户
        if ($this->isMaliciousUser($username)) {
            return;
        }

        if (!isset($this->currentLogin[$username])) {
            return;
        }

        $user = User::where('username', $username)->first();
        if (!$user) return;

        $login = $user->logins()->whereNull('logout_at')->latest()->first();
        if ($login) {
            // 确保登出时间大于登录时间
            if ($timestamp->lt($login->login_at)) {
                return;
            }

            $duration = $login->login_at->diffInSeconds($timestamp);
            
            // 确保时长为正数且合理
            if ($duration <= 0) {
                return;
            }

            $login->update([
                'logout_at' => $timestamp,
                'duration' => $duration
            ]);

            // 更新用户总在线时间
            $user->update([
                'is_online' => false,
                'last_logout_at' => $timestamp,
                'total_online_time' => max(0, $user->total_online_time + $duration)
            ]);

            // 更新每日统计
            $this->updateDailyStats($user, $login, $duration);
        }

        unset($this->currentLogin[$username]);
    }

    public function updateDailyStats($user, $login, $duration)
    {
        $date = $login->created_at->toDateString();
        $dailyStat = $user->dailyStats()->firstOrCreate(
            ['date' => $date],
            ['online_time' => 0]
        );
        $dailyStat->increment('online_time', $duration);
    }

    public function processLoginLocation($login, $user, $username, $logPath, $currentFile)
    {
        try {
            $filePath = $logPath . '/' . $currentFile;
            if (!File::exists($filePath)) {
                return;
            }
            
            $lines = File::lines($filePath)->toArray();
            foreach ($lines as $line) {
                if (preg_match('/^\[(.*?)\] \[Server thread\/INFO\]: ' . preg_quote($username) . '\[(.*?)\] logged in with entity id (\d+) at \(\[(.*?)\](.*?), (.*?), (.*?)\)/', $line, $matches)) {
                    $ip = trim(explode(':', $matches[2])[0], '/');
                    $entityId = $matches[3];
                    $world = $matches[4];
                    $x = (float) $matches[5];
                    $y = (float) $matches[6];
                    $z = (float) $matches[7];

                    LoginLocation::create([
                        'login_id' => $login->id,
                        'user_id' => $user->id,
                        'world' => $world,
                        'x' => $x,
                        'y' => $y,
                        'z' => $z,
                        'entity_id' => $entityId,
                        'ip' => $ip,
                    ]);
                    break;
                }
            }
        } catch (\Exception $e) {
            // 记录错误但继续执行
            \Log::warning("读取登录位置信息失败: " . $e->getMessage());
        }
    }

    public function handleChatMessage($username, $content, $timestamp)
    {
        // 检查是否是恶意用户
        if ($this->isMaliciousUser($username)) {
            return null;
        }

        $user = User::firstOrCreate(['username' => $username]);

        return ChatMessage::create([
            'user_id' => $user->id,
            'username' => $username,
            'content' => $content,
            'sent_at' => $timestamp,
            'created_at' => $timestamp
        ]);
    }

    public function findUuidFromLines($lines, $username)
    {
        // 如果已经在缓存中有这个用户的UUID，直接返回
        if (isset($this->uuidCache[$username])) {
            $uuid = $this->uuidCache[$username];
            unset($this->uuidCache[$username]); // 使用后清除缓存
            return $uuid;
        }

        // 在当前行之前查找UUID信息
        foreach ($lines as $line) {
            if (preg_match('/^\[(.*?)\] \[User Authenticator.*?\]: UUID of player ' . preg_quote($username) . ' is ([0-9a-f-]+)/', $line, $matches)) {
                return $matches[2];
            }
        }

        return null;
    }

    public function cacheUuid($username, $uuid)
    {
        $this->uuidCache[$username] = $uuid;
    }

    public function parseTimestamp($timeString, $currentFile)
    {
        // 从文件名获取日期
        if ($currentFile === 'latest.log') {
            $date = now()->toDateString();
        } else {
            // 从文件名提取日期，例如 2024-07-07-1.log
            if (!preg_match('/^(\d{4}-\d{2}-\d{2})-\d+\.log$/', $currentFile, $matches)) {
                throw new \Exception("无法从文件名 {$currentFile} 解析日期");
            }
            $date = $matches[1];
        }
        
        // 从日志行获取时间
        if (!preg_match('/(\d{2}:\d{2}:\d{2})/', $timeString, $matches)) {
            throw new \Exception("无法从日志行解析时间: {$timeString}");
        }
        $time = $matches[1];

        return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
    }
} 