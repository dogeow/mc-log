@extends('layouts.gradient')

@section('gradient-content')
<div class="container mx-auto p-4">
    <div class="bg-white/10 backdrop-blur rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">聊天记录</h2>
            <form action="{{ route('chat') }}" method="GET" class="flex space-x-2">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="搜索用户名或内容..." 
                       class="px-4 py-2 rounded-lg border border-gray-300 text-black focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    搜索
                </button>
                @if(request('search'))
                    <a href="{{ route('chat') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        清除
                    </a>
                @endif
            </form>
        </div>

        <section class="text-black">
            <!-- 移动端卡片视图 -->
            <div class="md:hidden space-y-4">
                @foreach($chatMessages as $message)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <img src="https://crafthead.net/avatar/{{ $message->username }}" alt="{{ $message->username }}" class="w-10 h-10 rounded-sm">
                        <div>
                            <div class="font-semibold">{{ $message->username }}</div>
                            <div class="text-sm text-gray-500">{{ $message->sent_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>
                    <div class="text-gray-700 break-words">
                        {{ auth()->check() && auth()->user()->is_admin ? $message->content : '*' }}
                    </div>
                </div>
                @endforeach
                
                <div class="mt-4">
                    {{ $chatMessages->appends(request()->query())->links() }}
                </div>
            </div>

            <!-- 桌面端表格视图 -->
            <div class="hidden md:block bg-white p-4 rounded-lg shadow overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">用户名</th>
                            <th class="px-4 py-2">消息内容</th>
                            <th class="px-4 py-2">时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chatMessages as $message)
                        <tr>
                            <td class="border px-4 py-2">
                                <div class="flex items-center space-x-2">
                                    <img src="https://crafthead.net/avatar/{{ $message->username }}" alt="{{ $message->username }}" class="w-6 h-6 rounded-sm">
                                    <span>{{ $message->username }}</span>
                                </div>
                            </td>
                            <td class="border px-4 py-2 break-words">{{ auth()->check() && auth()->user()->is_admin ? $message->content : '*' }}</td>
                            <td class="border px-4 py-2">{{ $message->sent_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="mt-4">
                    {{ $chatMessages->appends(request()->query())->links() }}
                </div>
            </div>
        </section>
    </div>
</div>
@endsection 