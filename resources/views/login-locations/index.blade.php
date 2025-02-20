@extends('layouts.gradient')

@section('gradient-content')
<div class="container mx-auto p-4">
    <div class="bg-white/10 backdrop-blur rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">登录位置</h3>
            <form action="{{ route('login-locations.index') }}" method="GET" class="flex space-x-2">
                <input type="search" name="search" 
                       class="px-4 py-2 rounded-lg border border-gray-300 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="搜索用户名..." 
                        value="{{ $search }}">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    搜索
                </button>
                @if(request('search'))
                    <a href="{{ route('login-locations.index') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        清除
                    </a>
                @endif
            </form>
        </div>

        <section class="text-black">
            <div class="bg-white p-4 rounded-lg shadow overflow-x-auto">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-left font-bold">
                            <th class="px-4 py-2">用户</th>
                            <th class="px-4 py-2">世界</th>
                            <th class="px-4 py-2">坐标</th>
                            <th class="px-4 py-2">IP地址</th>
                            <th class="px-4 py-2">登录时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $location)
                        <tr>
                            <td class="border px-4 py-2">
                                <div class="flex items-center space-x-2">
                                    <img src="https://crafthead.net/avatar/{{ $location->user->username }}" 
                                         alt="{{ $location->user->username }}" 
                                         class="w-6 h-6 rounded-sm">
                                    <span>{{ $location->user->username }}</span>
                                </div>
                            </td>
                            <td class="border px-4 py-2">{{ $location->world }}</td>
                            <td class="border px-4 py-2">{{ auth()->check() && auth()->user()->is_admin ? $location->formatted_coordinates : '(*, *, *)' }}</td>
                            <td class="border px-4 py-2">{{ auth()->check() && auth()->user()->is_admin ? $location->ip : '***' }}</td>
                            <td class="border px-4 py-2">{{ $location->login->login_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="border px-4 py-2 text-center">没有找到记录</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $locations->links() }}
                </div>
            </div>
        </section>
    </div>
</div>
@endsection 