<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\LoginLocation;
use Illuminate\Support\Facades\DB;
use App\Services\MinecraftServerStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $mcStatus = new MinecraftServerStatus();
        $serverStatus = $mcStatus->getServerStatus();
        
        return view('dashboard', compact('serverStatus'));
    }

    public function users(Request $request)
    {
        $query = User::with('loginLocations');

        // 默认排序：在线用户优先，然后按最后登录时间倒序
        $query->orderBy('is_online', 'desc')
              ->orderBy('last_login_at', 'desc');

        // 如果有其他排序参数，则应用它们
        $sort = $request->get('sort');
        $direction = $request->get('direction', 'asc');
        $allowedSorts = ['username', 'last_logout_at', 'total_online_time', 'is_scientist'];
        
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
        
        $users = $query->paginate(8);
        
        $dailyStats = ChatMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as message_count'),
            DB::raw('COUNT(DISTINCT user_id) as active_users')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->limit(30)
        ->get();

        return view('users', compact('users', 'dailyStats'));
    }

    public function chat()
    {
        $chatMessages = ChatMessage::with('user')
            ->orderBy('sent_at', 'desc')
            ->paginate(8);

        return view('chat', compact('chatMessages'));
    }
} 