@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <!-- 服务器状态部分 -->
    <div class="bg-gradient-to-r from-sky-500 to-indigo-500 text-white">
                <div class="flex justify-center space-x-4 items-center">
                    @if(isset($serverStatus['info']['favicon']))
                        <img src="{{ str_replace("\n", "", $serverStatus['info']['favicon']) }}" 
                             class="rounded border-2 border-[#444444]" style="width:64px;height:64px;">
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-[#55FF55]">
                            {{ $serverStatus['queryInfo']['GameName'] ?? '未知' }}
                        </h1>
                        <h2 class="text-[#AAAAAA]">{{ $serverStatus['queryInfo']['HostName'] ?? '未知' }}</h2>
                    </div>
                </div>

                <div class="mt-8 text-[#AAAAAA]">
                    版本：<span class="text-[#55FF55]">{{ $serverStatus['queryInfo']['Version'] ?? '未知' }}</span>
                    <span class="text-[#FFAA00]">{{ empty($serverStatus['queryInfo']['Plugins']) ? '原版服务器' : 'Mod 服务器' }}</span>
                    <span class="text-[#55FFFF]">{{ $serverStatus['queryInfo']['Software'] ?? '' }}</span>
                </div>
                
                <div class="text-[#AAAAAA]">
                    单人｜多人：<span class="text-[#55FF55]">{{ isset($serverStatus['queryInfo']['GameType']) && $serverStatus['queryInfo']['GameType'] === 'SMP' ? '多人游戏' : '单人游戏' }}</span>
                </div>
                <div class="mt-8 text-sm text-[#AAAAAA]">服务器查询用时 <span class="text-[#55FF55]">{{ $serverStatus['timer'] }}</span> 秒
                </div>

                <div class="mt-8 text-[#AAAAAA]">
                    在线人数：<span class="text-[#55FF55]">{{ $serverStatus['queryInfo']['Players'] ?? 0 }} / {{ $serverStatus['queryInfo']['MaxPlayers'] ?? 0 }}</span>
                </div>

                @if(!empty($serverStatus['players']))
                    <div>
                        <div class="text-[#FFAA00]">在线玩家：</div>
                        <div class="flex content-center space-x-2">
                            <div class="flex space-x-1 justify-center flex-wrap">
                                @foreach($serverStatus['players'] as $player)
                                    <div class="flex flex-col space-x-1 bg-[#2C2F33] p-2 rounded-lg border border-[#444444] m-1">
                                        <img src="https://minotar.net/cube/{{ $player }}/64.png" 
                                             class="w-8 h-8 mx-auto border border-[#444444]" alt="cube">
                                        <div class="text-[#55FF55]">{{ $player }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div style="border-bottom: 3rem solid;border-image: url(/images/minecraft_grass_block_texture.jpg) 1280 0 repeat;margin-top: 2rem">
                            <div class="flex items-center justify-center">
                                @foreach($serverStatus['players'] as $player)
                                    <img src="https://minotar.net/body/{{ $player }}/64.png" 
                                         class="h-24 mx-1" alt="body">
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
</div>

<style>
@font-face {
    font-family: 'Minecraft';
    src: url('/fonts/minecraft.ttf') format('truetype');
}
</style>
@endsection 