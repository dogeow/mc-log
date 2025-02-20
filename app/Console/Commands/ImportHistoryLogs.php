<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Login;
use App\Models\DailyStat;
use App\Models\ChatMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\LoginLocation;

class ImportHistoryLogs extends Command
{
    protected $signature = 'minecraft:import-history';
    protected $description = '导入历史日志文件';

    private $currentLogin = [];
    private $currentFile;
    private $uuidCache = [];  // 添加这个属性来缓存UUID
    private $logPath;

    // 添加恶意用户匹配规则
    private function isMaliciousUser($username)
    {
        return preg_match('/^Cornbread21\d{3,4}$/', $username);
    }

    public function __construct()
    {
        parent::__construct();
        $this->logPath = config('minecraft.log_path');
    }

    public function handle()
    {
        try {
            // 确保日志目录存在
            if (!File::exists($this->logPath)) {
                $this->error("日志目录不存在: {$this->logPath}");
                return 1;
            }

            $logFiles = collect(File::files($this->logPath))
                ->filter(function ($file) {
                    $filename = $file->getFilename();
                    // 只处理 latest.log 和符合日期格式的文件
                    return $filename === 'latest.log' || 
                           preg_match('/^\d{4}-\d{2}-\d{2}-\d+\.log$/', $filename);
                })
                ->sortBy(function ($file) {
                    $filename = $file->getFilename();
                    // 如果是 latest.log，返回一个很大的值确保排在最后
                    return $filename === 'latest.log' ? '9999-99-99' : $filename;
                });

            $this->info('找到 ' . $logFiles->count() . ' 个日志文件');

            // 清空现有数据
            if ($this->confirm('是否清空现有数据重新导入？')) {
                $this->info('清空数据...');
                
                // 清空数据
                Login::truncate();
                DailyStat::truncate();
                ChatMessage::truncate();
                User::query()->update(['total_online_time' => 0, 'is_online' => false]);
                
                $this->info('数据清空完成');
            }

            foreach ($logFiles as $file) {
                $this->currentFile = $file->getFilename();
                $this->info('处理文件: ' . $this->currentFile);
                
                try {
                    $lines = file($file->getPathname());
                    foreach ($lines as $line) {
                        // 添加UUID信息的处理
                        if (preg_match('/^\[(.*?)\] \[User Authenticator.*?\]: UUID of player (.*?) is ([0-9a-f-]+)/', $line, $matches)) {
                            $username = $matches[2];
                            $uuid = $matches[3];
                            $this->uuidCache[$username] = $uuid;
                            continue;
                        }

                        // 只处理以 [ 开头的行
                        if (!str_starts_with(trim($line), '[')) {
                            continue;
                        }

                        // 处理登录信息
                        if (preg_match('/^\[(.*?)\] \[Server thread\/INFO\]: (.*?) joined the game/', $line, $matches)) {
                            try {
                                $timestamp = $this->parseTimestamp($matches[1]);
                                $username = $matches[2];
                                $uuid = $this->findUuidFromLines($lines, $username);
                                $this->handleLogin($username, $uuid, $timestamp);
                            } catch (\Exception $e) {
                                $this->error("处理登录信息出错: " . $e->getMessage());
                            }
                        }
                        
                        // 处理登出信息
                        if (preg_match('/^\[(.*?)\] \[Server thread\/INFO\]: (.*?) left the game/', $line, $matches)) {
                            try {
                                $timestamp = $this->parseTimestamp($matches[1]);
                                $username = $matches[2];
                                $this->handleLogout($username, $timestamp);
                            } catch (\Exception $e) {
                                $this->error("处理登出信息出错: " . $e->getMessage());
                            }
                        }

                        // 处理 moved wrongly 信息
                        if (preg_match('/^\[(.*?)\] \[Server thread\/WARN\]: (.*?) moved wrongly!/', $line, $matches)) {
                            try {
                                $username = $matches[2];
                                $user = User::where('username', $username)->first();
                                if ($user && !$user->is_scientist) {
                                    $user->update(['is_scientist' => true]);
                                    $this->info("将用户 {$username} 标记为科学家");
                                }
                            } catch (\Exception $e) {
                                $this->error("处理科学家标记出错: " . $e->getMessage());
                            }
                        }

                        // 处理聊天消息
                        if (preg_match('/^\[(.*?)\] \[Async Chat Thread.*?\/INFO\]: \[Not Secure\] <(.*?)> (.*)/', $line, $matches)) {
                            try {
                                $timestamp = $this->parseTimestamp($matches[1]);
                                $username = $matches[2];
                                $content = $matches[3];

                                // 检查是否是恶意用户
                                if ($this->isMaliciousUser($username)) {
                                    $this->info("忽略恶意用户的聊天消息: {$username}");
                                    continue;
                                }

                                $user = User::firstOrCreate(['username' => $username]);

                                ChatMessage::create([
                                    'user_id' => $user->id,
                                    'username' => $username,
                                    'content' => $content,
                                    'sent_at' => $timestamp,
                                    'created_at' => $timestamp
                                ]);

                                $this->info("记录用户 {$username} 的聊天消息：{$content}");
                            } catch (\Exception $e) {
                                $this->error("处理聊天消息出错: " . $e->getMessage());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("处理文件 {$this->currentFile} 出错: " . $e->getMessage());
                    if (!$this->confirm('是否继续处理其他文件？')) {
                        break;
                    }
                }
            }

            // 处理未正常登出的记录
            foreach ($this->currentLogin as $username => $loginTime) {
                $this->handleLogout($username, now());
            }

            $this->info('历史日志导入完成');
        } catch (\Exception $e) {
            $this->error("导入过程出错: " . $e->getMessage());
        }
    }

    private function parseTimestamp($timeString)
    {
        // 从文件名获取日期
        if ($this->currentFile === 'latest.log') {
            $date = now()->toDateString();
        } else {
            // 从文件名提取日期，例如 2024-07-07-1.log
            if (!preg_match('/^(\d{4}-\d{2}-\d{2})-\d+\.log$/', $this->currentFile, $matches)) {
                throw new \Exception("无法从文件名 {$this->currentFile} 解析日期");
            }
            $date = $matches[1];
        }
        
        // 从日志行获取时间
        if (!preg_match('/(\d{2}:\d{2}:\d{2})/', $timeString, $matches)) {
            throw new \Exception("无法从日志行解析时间: {$timeString}");
        }
        $time = $matches[1];

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
        } catch (\Exception $e) {
            throw new \Exception("无法创建时间戳 {$date} {$time}: " . $e->getMessage());
        }
    }

    private function handleLogin($username, $uuid, $timestamp)
    {
        // 检查是否是恶意用户
        if ($this->isMaliciousUser($username)) {
            $this->info("忽略恶意用户: {$username}");
            return;
        }

        $user = User::firstOrCreate(['username' => $username, 'uuid' => $uuid]);
        
        // 如果用户已经在线，先处理之前的登录
        if (isset($this->currentLogin[$username])) {
            $this->handleLogout($username, $timestamp);
        }

        // 检查是否在1分钟内重新登录
        $lastLogin = $user->logins()->latest()->first();
        if ($lastLogin && $lastLogin->created_at->diffInMinutes($timestamp) <= 1) {
            return;
        }

        $this->currentLogin[$username] = $timestamp;

        $login = Login::create([
            'user_id' => $user->id,
            'login_at' => $timestamp,
            'created_at' => $timestamp
        ]);

        // 修改这里：使用完整的文件路径
        try {
            $filePath = $this->logPath . '/' . $this->currentFile;
            if (!File::exists($filePath)) {
                $this->warn("无法找到文件: {$filePath}");
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
            $this->warn("读取登录位置信息失败: " . $e->getMessage());
        }

        $user->update([
            'is_online' => true,
            'last_login_at' => $timestamp
        ]);

        $this->info("用户 {$username} 在 {$timestamp} 登录");
    }

    private function handleLogout($username, $timestamp)
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
                $this->warn("警告: {$username} 的登出时间 {$timestamp} 早于登录时间 {$login->login_at}，跳过处理");
                return;
            }

            $duration = $login->login_at->diffInSeconds($timestamp);
            
            // 确保时长为正数且合理
            if ($duration <= 0 || $duration > 86400) { // 86400 = 24小时
                $this->warn("警告: {$username} 的在线时长异常: {$duration} 秒，跳过处理");
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
                'total_online_time' => max(0, $user->total_online_time + $duration) // 确保总时间不为负
            ]);

            // 更新每日统计
            $this->updateDailyStats($user, $login, $duration);

            $this->info("用户 {$username} 在 {$timestamp} 登出，本次在线时长: " . gmdate('H:i:s', $duration));
        }

        unset($this->currentLogin[$username]);
    }

    private function updateDailyStats($user, $login, $duration)
    {
        $date = $login->created_at->toDateString();
        $dailyStat = $user->dailyStats()->firstOrCreate(
            ['date' => $date],
            ['online_time' => 0]
        );
        $dailyStat->increment('online_time', $duration);
    }

    private function findUuidFromLines($lines, $username)
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

        // 如果没找到UUID，抛出异常
        throw new \Exception("无法找到用户 {$username} 的 UUID");
    }
} 