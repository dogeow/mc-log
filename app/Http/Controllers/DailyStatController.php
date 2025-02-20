<?php

namespace App\Http\Controllers;

use App\Models\DailyStat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DailyStatController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyStat::query()
            ->with('user')
            ->latest('date');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        $dailyStats = $query->paginate(10);

        return view('daily-stats.index', [
            'dailyStats' => $dailyStats,
            'search' => $request->search
        ]);
    }
} 