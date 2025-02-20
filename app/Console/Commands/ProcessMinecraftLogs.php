<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MinecraftLogService;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProcessMinecraftLogs extends Command
{
    protected $signature = 'minecraft:process-logs';
    protected $description = '处理 Minecraft 服务器日志';

    private $logFile;
    private $logService;
    private $lastProcessedLine;

    public function __construct(MinecraftLogService $logService)
    {
        parent::__construct();
        $this->logPath = config('minecraft.log_path');
        $this->logFile = $this->logPath . '/latest.log';
        $this->logService = $logService;
    }

    public function handle()
    {
        if (!file_exists($this->logFile)) {
            return;
        }

        $lines = file($this->logFile);
        $totalLines = count($lines);

        // 获取上次处理的行数
        $this->lastProcessedLine = Cache::get('minecraft_log_last_line', 0);

        // 如果文件行数小于上次处理的行数,说明是新文件,重置为0
        if ($totalLines < $this->lastProcessedLine) {
            $this->lastProcessedLine = 0;
        }

        // 从上次处理的位置继续处理
        for ($i = $this->lastProcessedLine; $i < $totalLines; $i++) {
            $line = $lines[$i];

            // 处理UUID信息
            if (preg_match('/\[.*?\] \[User Authenticator.*?\]: UUID of player (.*?) is ([0-9a-f-]+)/', $line, $matches)) {
                $username = $matches[1];
                $uuid = $matches[2];
                $this->logService->cacheUuid($username, $uuid);
                continue;
            }
            
            // 处理登录信息
            if (preg_match('/\[.*?\] \[Craft Scheduler Thread.*?AuthMe\/INFO\]: \[AuthMe\] (.*?) logged in/', $line, $matches)) {
                $username = $matches[1];
                $uuid = $this->logService->findUuidFromLines($lines, $username);
                $timestamp = $this->logService->parseTimestamp($line, 'latest.log');
                
                // 更新用户最后登录时间
                User::where('username', $username)->update(['last_login_at' => $timestamp]);
                
                $this->logService->handleLogin($username, $uuid, $timestamp, dirname($this->logFile), 'latest.log');
            }
            
            // 处理登出信息
            if (preg_match('/\[.*?\] \[Server thread\/INFO\]: (.*?) left the game/', $line, $matches)) {
                $username = $matches[1];
                $timestamp = $this->logService->parseTimestamp($line, 'latest.log');
                
                // 获取用户最后登录时间
                $user = User::where('username', $username)->first();
                if ($user) {
                    $lastLoginAt = $user->last_login_at;
                    $this->logService->handleLogout($username, $timestamp, $lastLoginAt);
                }
            }

            // 处理聊天消息
            if (preg_match('/\[.*?\] \[Async Chat Thread.*?\/INFO\]: \[Not Secure\] <(.*?)> (.*)/', $line, $matches)) {
                $username = $matches[1];
                $content = $matches[2];
                $timestamp = $this->logService->parseTimestamp($line, 'latest.log');
                $this->logService->handleChatMessage($username, $content, $timestamp);
            }
        }

        // 保存最后处理的行数
        Cache::put('minecraft_log_last_line', $totalLines, now()->addDays(1));

        $this->info('处理到' . $totalLines . '行');
    }
}