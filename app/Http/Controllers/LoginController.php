<?php

namespace App\Http\Controllers;

use App\Models\Login;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $query = Login::query()
            ->with('user')
            ->latest('login_at');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        $logins = $query->paginate(10);

        return view('logins.index', [
            'logins' => $logins,
            'search' => $request->search
        ]);
    }
} 