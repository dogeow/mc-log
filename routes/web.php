<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/users', [DashboardController::class, 'users'])->name('users');
Route::get('/chat', [DashboardController::class, 'chat'])->name('chat');
