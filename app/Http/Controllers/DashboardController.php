<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Hitung jumlah Board berdasarkan status (kecuali Closed)
        $pendingCount = BoardCard::where('user_id', $userId)
            ->where('status', 'Pending')
            ->count();

        $inProgressCount = BoardCard::where('user_id', $userId)
            ->where('status', 'In Progress')
            ->count();

        $completedCount = BoardCard::where('user_id', $userId)
            ->where('status', 'Completed')
            ->count();

        // Ambil 3 board terakhir untuk Overview (kecuali yang sudah Closed)
        $recentBoards = BoardCard::where('user_id', $userId)
            ->whereNull('closed_at') // ⬅️ Tambahkan filter ini
            ->with(['user', 'members', 'tasks'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($board) {
                $total = $board->tasks->count();
                $done = $board->tasks->filter(function ($t) {
                    if (isset($t->is_done)) {
                        return (bool) $t->is_done;
                    }
                    if (isset($t->is_completed)) {
                        return (bool) $t->is_completed;
                    }
                    if (isset($t->done)) {
                        return (bool) $t->done;
                    }
                    return false;
                })->count();

                $progress = $total > 0 ? round(($done / $total) * 100) : 0;

                return [
                    'id' => $board->id,
                    'title' => $board->title,
                    'deadline' => $board->deadline,
                    'members' => $board->members->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'name' => $m->name,
                            'photo' => $m->photo ?? null,
                        ];
                    })->toArray(),
                    'progress' => $progress,
                ];
            });

        // Ambil 3 user terbaru
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Ambil semua board yang punya deadline (kecuali yang Closed)
        $deadlines = BoardCard::where('user_id', $userId)
            ->whereNull('closed_at') // ⬅️ Tambahkan filter ini juga
            ->whereNotNull('deadline')
            ->select('id', 'title', 'deadline')
            ->get();

        // Ambil semua board dengan deadline hari ini (kecuali yang Closed)
        $todayTasks = BoardCard::where('user_id', $userId)
            ->whereNull('closed_at') // ⬅️ Tambahkan filter ini juga
            ->whereDate('deadline', now())
            ->select('id', 'title', 'deadline')
            ->get();

        return Inertia::render('Dashboard', [
            'pendingCount' => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'recentBoards' => $recentBoards,
            'recentUsers' => $recentUsers,
            'deadlines' => $deadlines,
            'todayTasks' => $todayTasks,
        ]);
    }
}
