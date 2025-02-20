@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 space-y-8">
    <h1 class="text-2xl font-bold mb-4">用户列表</h1>
    
    <section>
        <!-- 移动端卡片视图 -->
        <div class="md:hidden space-y-4">
            @foreach($users as $user)
            <div class="bg-white rounded-lg shadow p-4 {{ $user->is_online ? 'border-l-4 border-green-500' : '' }}">
                <div class="flex items-center space-x-3 mb-3">
                    <img src="https://crafthead.net/avatar/{{ $user->username }}" alt="{{ $user->username }}" class="w-10 h-10 rounded-sm">
                    <div>
                        <div class="font-semibold">{{ $user->username }}</div>
                        <span class="px-2 py-1 rounded text-sm {{ $user->is_online ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{$user->is_online ? '在线' : '离线'}}
                        </span>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>离线时间：</span>
                        <span>{{ $user->last_logout_at }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>所在世界：</span>
                        <span>
                            @if($lastLocation = $user->loginLocations->last())
                                {{ $lastLocation->world }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>总在线时长：</span>
                        <span>{{ gmdate('H:i:s', $user->total_online_time) }}</span>
                    </div>
                    @if($user->is_scientist)
                    <div class="flex justify-end">
                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">科学家</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            
            <div class="mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>

        <!-- 桌面端表格视图 -->
        <div class="hidden md:block bg-white p-4 rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'username', 'direction' => request('direction') === 'asc' && request('sort') === 'username' ? 'desc' : 'asc']) }}" class="flex items-center">
                                用户名
                                @if(request('sort') === 'username')
                                    <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-2">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_logout_at', 'direction' => request('direction') === 'asc' && request('sort') === 'last_logout_at' ? 'desc' : 'asc']) }}" class="flex items-center">
                                离线时间
                                @if(request('sort') === 'last_logout_at')
                                    <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-2">状态</th>
                        <th class="px-4 py-2">世界</th>
                        <th class="px-4 py-2">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_online_time', 'direction' => request('direction') === 'asc' && request('sort') === 'total_online_time' ? 'desc' : 'asc']) }}" class="flex items-center">
                                总在线时长
                                @if(request('sort') === 'total_online_time')
                                    <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-2">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_scientist', 'direction' => request('direction') === 'asc' && request('sort') === 'is_scientist' ? 'desc' : 'asc']) }}" class="flex items-center">
                                标记
                                @if(request('sort') === 'is_scientist')
                                    <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="{{ $user->is_online ? 'bg-green-50' : '' }}">
                        <td class="border px-4 py-2">
                            <div class="flex items-center space-x-2">
                                <img src="https://crafthead.net/avatar/{{ $user->username }}" alt="{{ $user->username }}" class="w-6 h-6 rounded-sm">
                                <span>{{ $user->username }}</span>
                            </div>
                        </td>
                        <td class="border px-4 py-2">{{ $user->last_logout_at }}</td>
                        <td class="border px-4 py-2">
                            <span class="px-2 py-1 rounded text-sm {{ $user->is_online ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{$user->is_online ? '在线' : '离线'}}
                            </span>
                        </td>
                        <td class="border px-4 py-2">
                            @if($lastLocation = $user->loginLocations->last())
                                {{ $lastLocation->world }}
                            @endif
                        </td>
                        <td class="border px-4 py-2">
                            {{ gmdate('H:i:s', $user->total_online_time) }}
                        </td>
                        <td class="border px-4 py-2">
                            @if($user->is_scientist)
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">科学家</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
    
    <section>
        <h2 class="text-xl font-bold mb-4">每日统计</h2>
        <div class="bg-white p-4 rounded-lg shadow">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">日期</th>
                        <th class="px-4 py-2">消息数量</th>
                        <th class="px-4 py-2">活跃用户</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyStats as $stat)
                    <tr>
                        <td class="border px-4 py-2">{{ $stat->date }}</td>
                        <td class="border px-4 py-2">{{ $stat->message_count }}</td>
                        <td class="border px-4 py-2">{{ $stat->active_users }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection 