@extends('layouts.gradient')

@section('gradient-content')
<div class="container mx-auto p-4">
    <div class="bg-white/10 backdrop-blur rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">聊天记录</h2>
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
                        {{ $message->content }}
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
                            <td class="border px-4 py-2 break-words">{{ $message->content }}</td>
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