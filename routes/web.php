<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BordController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Board Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/board', [BordController::class, 'index'])->name('board.index');
    Route::get('/board/{id}', [BordController::class, 'show'])->name('board.show');
    Route::post('/board/create', [BordController::class, 'store'])->name('board.card.store');
    Route::put('/board/{id}', [BordController::class, 'update'])->name('board.card.update');
    Route::delete('/board/{id}', [BordController::class, 'destroy'])->name('board.card.destroy');
    Route::post('/board/{id}/tasks', [BordController::class, 'addTask'])->name('board.task.store');
    Route::post('/board/tasks/{id}/toggle', [BordController::class, 'toggleTask'])->name('task.toggle');
    Route::post('/board/{id}/invite', [BordController::class, 'inviteMember'])->name('board.invite');
    Route::put('/board/{card}/subtasks', [BordController::class, 'updateSubtasks'])->name('board.subtasks.update');
    Route::put('/board/{id}/close', [BordController::class, 'close'])->name('board.close');
});


//Member
Route::get('/member', [MemberController::class, 'index'])->name('member.index');
Route::delete('/member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');

// History
Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
