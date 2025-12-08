<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\CommentController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Board Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/board', [BoardController::class, 'index'])->name('board.index');
    Route::get('/board/{id}', [BoardController::class, 'show'])->name('board.show');
    Route::post('/board/create', [BoardController::class, 'store'])->name('board.card.store');
    Route::put('/board/{id}', [BoardController::class, 'update'])->name('board.card.update');
    Route::delete('/board/{id}', [BoardController::class, 'destroy'])->name('board.card.destroy');
    Route::post('/board/{id}/tasks', [BoardController::class, 'addTask'])->name('board.task.store');
    Route::post('/board/tasks/{id}/toggle', [BoardController::class, 'toggleTask'])->name('task.toggle');
    Route::post('/board/{id}/invite', [BoardController::class, 'inviteMember'])->name('board.invite');
    Route::put('/board/{card}/subtasks', [BoardController::class, 'updateSubtasks'])->name('board.subtasks.update');
    Route::put('/board/{id}/close', [BoardController::class, 'close'])->name('board.close');
    Route::delete('/board/{card}/leave', [BoardController::class, 'leaveCard'])->name('board.leave');
    Route::delete('/board/{card}/remove/{user}', [BoardController::class, 'removeMember'])->name('board.remove');
});


//Member
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/member', [MemberController::class, 'index'])->name('member.index');
    Route::delete('/member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');
});

// History
Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');

//Detail Subtask
Route::get('/subtask/{id}', [SubTaskController::class, 'show'])->name('subtask.show');
Route::put('/subtask/{id}', [SubTaskController::class, 'update'])->name('subtask.update'); //update judal dan deskripsi subtask

Route::middleware(['auth', 'verified'])->group(function () {
    // Comments API
    Route::get('/subtasks/{id}/comments', [CommentController::class, 'index']);
    Route::post('/subtasks/{id}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comment.update'); 
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comment.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';