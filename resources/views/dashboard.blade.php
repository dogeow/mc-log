@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-r from-sky-500 to-indigo-500 text-white flex flex-col">
        <!-- 导航栏 -->
        <nav class="bg-transparent">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center py-3">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('home') }}" class="text-xl font-bold text-white">MC Log</a>
                        <button class="md:hidden rounded-lg focus:outline-none focus:shadow-outline" id="menuBtn">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-6 h-6 text-white">
                                <path id="menuIcon" fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z"></path>
                                <path id="closeIcon" class="hidden" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="hidden md:flex md:items-center md:space-x-4" id="menu">
                        <a href="{{ route('home') }}" class="block mt-4 md:inline-block md:mt-0 text-white hover:text-gray-200 {{ request()->routeIs('home') ? 'font-semibold' : '' }}">首页</a>
                        <a href="{{ route('users') }}" class="block mt-4 md:inline-block md:mt-0 text-white hover:text-gray-200 {{ request()->routeIs('users') ? 'font-semibold' : '' }}">用户列表</a>
                        <a href="{{ route('chat') }}" class="block mt-4 md:inline-block md:mt-0 text-white hover:text-gray-200 {{ request()->routeIs('chat') ? 'font-semibold' : '' }}">聊天记录</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- 内容区域 -->
        <div class="flex-1 flex flex-col justify-between">
            <div class="container mx-auto">
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
                <div class="mt-8 text-sm text-[#AAAAAA]">
                    服务器查询用时 <span class="text-[#55FF55]">{{ $serverStatus['timer'] }}</span> 秒
                </div>

                <div class="mt-8 text-[#AAAAAA]">
                    在线人数：<span class="text-[#55FF55]">{{ $serverStatus['queryInfo']['Players'] ?? 0 }} / {{ $serverStatus['queryInfo']['MaxPlayers'] ?? 0 }}</span>
                </div>

                @if(!empty($serverStatus['players']))
                    <div class="mt-8">
                        <div class="text-[#FFAA00] mb-4">在线玩家：</div>
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
                    </div>
                @endif
            </div>

            @if(!empty($serverStatus['players']))
                <div style="border-bottom: 3rem solid;border-image: url(/images/minecraft_grass_block_texture.jpg) 1280 0 repeat;">
                    <div class="flex items-center justify-center">
                        @foreach($serverStatus['players'] as $player)
                            <img src="https://minotar.net/body/{{ $player }}/64.png" 
                                 class="h-24 mx-1" alt="body">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
    @font-face {
        font-family: 'Minecraft';
        src: url('/fonts/minecraft.ttf') format('truetype');
    }
    </style>
@endsection