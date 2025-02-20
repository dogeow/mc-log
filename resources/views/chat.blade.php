@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 space-y-8">
    <h1 class="text-2xl font-bold mb-4">聊天记录</h1>
    
    <section>
        <div class="bg-white p-4 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">时间</th>
                        <th class="px-4 py-2">用户名</th>
                        <th class="px-4 py-2">消息内容</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chatMessages as $message)
                    <tr>
                        <td class="border px-4 py-2">{{ $message->sent_at->format('Y-m-d H:i:s') }}</td>
                        <td class="border px-4 py-2">
                            <div class="flex items-center space-x-2">
                                <img src="https://crafthead.net/avatar/{{ $message->username }}" alt="{{ $message->username }}" class="w-6 h-6 rounded-sm">
                                <span>{{ $message->username }}</span>
                            </div>
                        </td>
                        <td class="border px-4 py-2">{{ $message->content }}</td>
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
@endsection 