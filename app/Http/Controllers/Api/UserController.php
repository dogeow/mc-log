<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DailyStat;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        return User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'is_online' => $user->is_online,
                'total_online_time' => $user->total_online_time,
                'avatar_url' => "https://crafthead.net/avatar/{$user->username}",
                'skin_url' => "https://crafthead.net/skin/{$user->username}"
            ];
        });
    }

    public function dailyStats(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        
        return DailyStat::with('user')
            ->whereDate('date', $date)
            ->get()
            ->map(function ($stat) {
                return [
                    'username' => $stat->user->username,
                    'online_time' => $stat->online_time,
                    'avatar_url' => "https://crafthead.net/avatar/{$stat->user->username}"
                ];
            });
    }

    public function yearlyCalendar()
    {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        $stats = DailyStat::selectRaw('date, COUNT(DISTINCT user_id) as user_count')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        return $stats->map(function ($stat) {
            return [
                'date' => $stat->date,
                'count' => $stat->user_count
            ];
        });
    }
} 