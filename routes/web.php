<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BordController;
use App\Http\Controllers\MemberController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Board Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/board', [BordController::class, 'index'])->name('board');
    Route::get('/board/{id}', [BordController::class, 'show'])->name('board.show');
    Route::post('/board/create', [BordController::class, 'store']);
    Route::put('/board/{id}', [BordController::class, 'update']);
    Route::delete('/board/{id}', [BordController::class, 'destroy']);
    Route::post('/board/{id}/tasks', [BordController::class, 'addTask']);
    Route::post('/board/tasks/{id}/toggle', [BordController::class, 'toggleTask'])->name('task.toggle');
});


// Member
Route::get('/member', [MemberController::class, 'index'])->middleware(['auth', 'verified'])->name('member');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
