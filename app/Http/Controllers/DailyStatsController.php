<?php

namespace App\Http\Controllers;

use App\Models\DailyStat;
use Illuminate\Http\Request;

class DailyStatsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $dailyStats = DailyStat::with('user')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('username', 'like', "%{$search}%");
                });
            })
            ->latest('date')
            ->paginate(15);

        return view('daily-stats.index', compact('dailyStats', 'search'));
    }
} 