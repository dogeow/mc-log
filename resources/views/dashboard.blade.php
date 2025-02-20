@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#1D1F21]">
    <div class="container mx-auto p-4">
        <div class="bg-[#2C2F33] rounded-lg shadow-2xl p-6 text-center border-2 border-[#373A3F]">
            <h1 class="text-4xl font-bold mb-4 text-[#55FF55]" style="font-family: 'Minecraft', monospace;">MC Log</h1>
            <p class="text-[#AAAAAA] mb-6">这里记录了 Minecraft 服务器的各种统计数据</p>
            
            <!-- 服务器状态部分 -->
            <div class="mb-8 p-6 bg-gradient-to-r from-sky-500 to-indigo-500 rounded-lg text-white">
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

                <div class="mt-8 text-[#AAAAAA]">
                    在线人数：<span class="text-[#55FF55]">{{ $serverStatus['queryInfo']['Players'] ?? 0 }} / {{ $serverStatus['queryInfo']['MaxPlayers'] ?? 0 }}</span>
                </div>

                @if(!empty($serverStatus['players']))
                    <div class="mt-4">
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

                <div class="mt-8 text-sm text-[#AAAAAA]">
                    服务器查询用时 <span class="text-[#55FF55]">{{ $serverStatus['timer'] }}</span> 秒
                </div>
            </div>

            <!-- 功能卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <a href="{{ route('users') }}" 
                   class="block p-6 bg-[#373A3F] rounded-lg border-2 border-[#444444] hover:bg-[#3F4245] transition duration-300 group">
                    <h2 class="text-xl font-bold mb-2 text-[#55FF55] group-hover:text-[#5FFF5F]">用户列表</h2>
                    <p class="text-[#AAAAAA]">查看所有玩家的在线状态和游戏时长</p>
                </a>
                
                <a href="{{ route('chat') }}" 
                   class="block p-6 bg-[#373A3F] rounded-lg border-2 border-[#444444] hover:bg-[#3F4245] transition duration-300 group">
                    <h2 class="text-xl font-bold mb-2 text-[#55FF55] group-hover:text-[#5FFF5F]">聊天记录</h2>
                    <p class="text-[#AAAAAA]">浏览服务器的历史聊天记录</p>
                </a>
            </div>
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