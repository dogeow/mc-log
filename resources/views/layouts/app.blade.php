<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minecraft 服务器统计</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow mb-8">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="{{ route('home') }}" class="text-xl font-bold">MC Log</a>
                <div class="space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('home') ? 'text-gray-900 font-semibold' : '' }}">首页</a>
                    <a href="{{ route('users') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('users') ? 'text-gray-900 font-semibold' : '' }}">用户列表</a>
                    <a href="{{ route('chat') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('chat') ? 'text-gray-900 font-semibold' : '' }}">聊天记录</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-white shadow mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600">
            &copy; {{ date('Y') }} MC Log. All rights reserved.
        </div>
    </footer>
</body>
</html> 