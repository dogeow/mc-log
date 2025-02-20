<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MinecraftLogService;

class ProcessMinecraftLogs extends Command
{
    protected $signature = 'minecraft:process-logs';
    protected $description = '处理 Minecraft 服务器日志';

    private $logFile = './logs/latest.log';
    private $logService;

    public function __construct(MinecraftLogService $logService)
    {
        parent::__construct();
        $this->logService = $logService;
    }

    public function handle()
    {
        if (!file_exists($this->logFile)) {
            return;
        }

        $lines = file($this->logFile);

        foreach ($lines as $line) {
            // 处理UUID信息
            if (preg_match('/\[.*?\] \[User Authenticator.*?\]: UUID of player (.*?) is ([0-9a-f-]+)/', $line, $matches)) {
                $username = $matches[1];
                $uuid = $matches[2];
                $this->logService->cacheUuid($username, $uuid);
                continue;
            }
            
            // 处理登录信息
            if (preg_match('/\[.*?\] \[Server thread\/INFO\]: (.*?) joined the game/', $line, $matches)) {
                $username = $matches[1];
                $uuid = $this->logService->findUuidFromLines($lines, $username);
                $timestamp = $this->logService->parseTimestamp($line, 'latest.log');
                $this->logService->handleLogin($username, $uuid, $timestamp, dirname($this->logFile), 'latest.log');
            }
            
            // 处理登出信息
            if (preg_match('/\[.*?\] \[Server thread\/INFO\]: (.*?) left the game/', $line, $matches)) {
                $username = $matches[1];
                $timestamp = $this->logService->parseTimestamp($line, 'latest.log');
                $this->logService->handleLogout($username, $timestamp);
            }

            // 处理聊天消息
            if (preg_match('/\[.*?\] \[Async Chat Thread.*?\/INFO\]: \[Not Secure\] <(.*?)> (.*)/', $line, $matches)) {
                $username = $matches[1];
                $content = $matches[2];
                $timestamp = $this->logService->parseTimestamp($line, 'latest.log');
                $this->logService->handleChatMessage($username, $content, $timestamp);
            }
        }
    }
}