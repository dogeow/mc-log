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
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center py-3">
                <div class="flex justify-between items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold">MC Log</a>
                    <button class="md:hidden rounded-lg focus:outline-none focus:shadow-outline" id="menuBtn">
                        <svg fill="currentColor" viewBox="0 0 20 20" class="w-6 h-6">
                            <path id="menuIcon" fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z"></path>
                            <path id="closeIcon" class="hidden" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"></path>
                        </svg>
                    </button>
                </div>
                <div class="hidden md:flex md:items-center md:space-x-4" id="menu">
                    <a href="{{ route('home') }}" class="block mt-4 md:inline-block md:mt-0 text-gray-600 hover:text-gray-900 {{ request()->routeIs('home') ? 'text-gray-900 font-semibold' : '' }}">首页</a>
                    <a href="{{ route('users') }}" class="block mt-4 md:inline-block md:mt-0 text-gray-600 hover:text-gray-900 {{ request()->routeIs('users') ? 'text-gray-900 font-semibold' : '' }}">用户列表</a>
                    <a href="{{ route('chat') }}" class="block mt-4 md:inline-block md:mt-0 text-gray-600 hover:text-gray-900 {{ request()->routeIs('chat') ? 'text-gray-900 font-semibold' : '' }}">聊天记录</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script>
        document.getElementById('menuBtn').addEventListener('click', function() {
            const menu = document.getElementById('menu');
            const menuIcon = document.getElementById('menuIcon');
            const closeIcon = document.getElementById('closeIcon');
            
            menu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });
    </script>
</body>
</html> 