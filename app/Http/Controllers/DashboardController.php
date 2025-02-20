<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\LoginLocation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('loginLocations');

        // 处理排序
        $sort = $request->get('sort', 'username');
        $direction = $request->get('direction', 'asc');
        $allowedSorts = ['username', 'last_logout_at', 'is_online', 'total_online_time', 'is_scientist'];
        
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
        
        $users = $query->paginate(10);
        
        $dailyStats = ChatMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as message_count'),
            DB::raw('COUNT(DISTINCT user_id) as active_users')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->limit(30)
        ->get();

        return view('dashboard', compact('users', 'dailyStats'));
    }
} 