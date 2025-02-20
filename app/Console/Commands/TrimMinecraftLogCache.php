<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TrimMinecraftLogCache extends Command
{
    protected $signature = 'minecraft:set-cache {value : 要设置的缓存值}';
    protected $description = '设置 minecraft_log_last_line 缓存的值';

    public function handle()
    {
        $value = $this->argument('value');
        
        // 更新缓存
        Cache::put('minecraft_log_last_line', $value);

        $this->info("已更新缓存值为: $value");
    }
} 