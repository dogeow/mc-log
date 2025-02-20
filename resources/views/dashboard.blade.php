@extends('layouts.gradient')

@section('gradient-content')
    <div class="container mx-auto">
        <div class="flex justify-center space-x-4 items-center">
            @if(isset($serverStatus['info']['favicon']))
                <img src="{{ str_replace("\n", "", $serverStatus['info']['favicon']) }}" style="width:64px;height:64px;">
            @endif
            <div>
                <h1 class="text-3xl font-bold text-[#55FF55]">
                    {{ $serverStatus['queryInfo']['GameName'] ?? '未知' }}
                </h1>
                <h2 class="text-[#AAAAAA]">{{ $serverStatus['queryInfo']['HostName'] ?? '未知' }}</h2>
            </div>
        </div>

        <div class="flex flex-col items-center space-y-4">
            <div class="text-[#AAAAAA]">
                版本：<span class="text-[#55FF55]">{{ $serverStatus['queryInfo']['Version'] ?? '未知' }}</span>
                {{ empty($serverStatus['queryInfo']['Plugins']) ? '原版服务器' : 'Mod 服务器' }}
                {{ $serverStatus['queryInfo']['Software'] ?? '' }}
            </div>
            
            <div class="text-[#AAAAAA]">
                单人｜多人：{{ isset($serverStatus['queryInfo']['GameType']) && $serverStatus['queryInfo']['GameType'] === 'SMP' ? '多人游戏' : '单人游戏' }}
            </div>

            <div class="text-sm text-[#AAAAAA]">
                服务器查询用时{{ $serverStatus['timer'] }}秒
            </div>
        </div>

        @if(!empty($serverStatus['players']))
            <div class="mt-8">
                <div class="text-[#FFAA00] mb-4">在线玩家：{{ $serverStatus['queryInfo']['Players'] ?? 0 }} / {{ $serverStatus['queryInfo']['MaxPlayers'] ?? 0 }}</div>
                <div class="flex content-center space-x-2">
                    <div class="flex space-x-1 justify-center flex-wrap">
                        @foreach($serverStatus['players'] as $player)
                            <div class="flex flex-col space-x-1 bg-white/10 backdrop-blur p-2 rounded-lg border border-[#FFAA00] m-1">
                                <img src="https://minotar.net/cube/{{ $player }}/64.png" 
                                     class="w-8 h-8 mx-auto" alt="cube">
                                <div>{{ $player }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(!empty($serverStatus['players']))
        <div style="border-bottom: 3rem solid;border-image: url(/images/minecraft_grass_block_texture.jpg) 1280 0 repeat;">
            <div class="flex items-center justify-center">
                @foreach($serverStatus['players'] as $player)
                    <img src="https://minotar.net/body/{{ $player }}/64.png" class="h-24 mx-1" alt="body">
                @endforeach
            </div>
        </div>
    @endif
@endsection