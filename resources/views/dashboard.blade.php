@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white rounded-lg shadow-lg p-6 text-center">
        <h1 class="text-3xl font-bold mb-4">欢迎来到 MC Log</h1>
        <p class="text-gray-600 mb-6">这里记录了 Minecraft 服务器的各种统计数据</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <a href="{{ route('users') }}" class="block p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <h2 class="text-xl font-semibold mb-2">用户列表</h2>
                <p class="text-gray-600">查看所有玩家的在线状态和游戏时长</p>
            </a>
            
            <a href="{{ route('chat') }}" class="block p-6 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <h2 class="text-xl font-semibold mb-2">聊天记录</h2>
                <p class="text-gray-600">浏览服务器的历史聊天记录</p>
            </a>
        </div>
    </div>
</div>
@endsection 