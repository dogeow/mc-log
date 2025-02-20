<?php

namespace App\Services;

use App\Services\Minecraft\MinecraftPing;
use App\Services\Minecraft\MinecraftQuery;

class MinecraftServerStatus
{
    const MQ_SERVER_ADDR = '8.210.157.33';
    const MQ_SERVER_PORT = 25565;
    const MQ_SERVER_QUERY_PORT = 25565;
    const MQ_TIMEOUT = 1;

    public function getServerStatus()
    {
        $timer = microtime(true);
        
        $info = $this->createPing(self::MQ_SERVER_ADDR, self::MQ_SERVER_PORT, self::MQ_TIMEOUT);
        list($queryInfo, $players) = $this->createQuery(self::MQ_SERVER_ADDR, self::MQ_SERVER_QUERY_PORT);
        
        $timer = number_format(microtime(true) - $timer, 4, '.', '');

        // 确保 players 始终是数组
        $players = is_array($players) ? $players : [];
        
        // 确保 queryInfo 始终是数组
        $queryInfo = is_array($queryInfo) ? $queryInfo : [];
        
        // 设置默认值
        $queryInfo = array_merge([
            'GameName' => '未知',
            'HostName' => '未知',
            'Version' => '未知',
            'Plugins' => '',
            'Software' => '',
            'GameType' => '',
            'Players' => 0,
            'MaxPlayers' => 0,
        ], $queryInfo);

        return [
            'info' => $info,
            'queryInfo' => $queryInfo,
            'players' => $players,
            'timer' => $timer
        ];
    }

    private function createPing($host, $port, $timeout)
    {
        try {
            $ping = new MinecraftPing($host, $port, $timeout);
            return $ping->Query();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function createQuery($host, $port)
    {
        try {
            $query = new MinecraftQuery();
            $query->Connect($host, $port);
            $info = $query->GetInfo();
            $players = $query->GetPlayers();
            
            // 确保返回值是数组
            return [
                is_array($info) ? $info : [],
                is_array($players) ? $players : []
            ];
        } catch (\Exception $e) {
            return [[], []];
        }
    }
} 