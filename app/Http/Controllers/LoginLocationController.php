<?php

namespace App\Http\Controllers;

use App\Models\LoginLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginLocation::query()
            ->with(['user', 'login'])
            ->latest();

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        $locations = $query->paginate(10);

        return view('login-locations.index', [
            'locations' => $locations,
            'search' => $request->search
        ]);
    }
} 