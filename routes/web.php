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

// ✅ TAMBAHKAN INI 🔥
Route::post('/subtask/{subtask}/close', [SubTaskController::class, 'close'])
    ->middleware(['auth', 'verified'])
    ->name('subtask.close'); 
// ...

Route::middleware(['auth', 'verified'])->group(function () {
    // GET Comments (Menggunakan Model Binding {subtask} dan method getComments)
    Route::get('/subtasks/{subtask}/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::get('/subtask/{id}', [SubTaskController::class, 'show'])->name('subtask.show');

    // ⭐ ROUTE MARK AS READ BARU ⭐
    Route::post('/subtask/{subtask}/mark-read', [SubTaskController::class, 'markAsRead'])
    ->middleware(['auth', 'verified'])
    ->name('subtask.markRead');
    // POST Comments (Store/Reply)
    Route::post('/subtasks/{subtask}/comments', [CommentController::class, 'store'])->name('comments.store');
    
    // PUT/DELETE Comments (Edit/Hapus - Menggunakan Model Binding {comment})
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update'); 
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';