<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BordController;
use App\Http\Controllers\MemberController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/board', [BordController::class, 'index'])->middleware(['auth', 'verified'])->name('board');

Route::get('/member', [MemberController::class, 'index'])->middleware(['auth', 'verified'])->name('member');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
