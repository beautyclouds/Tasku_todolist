<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil ID user yang sedang login
        $userId = Auth::id();

        // Hitung jumlah Board berdasarkan status, tapi hanya untuk user yang sedang login
        $pendingCount = BoardCard::where('user_id', $userId)->where('status', 'Pending')->count();
        $inProgressCount = BoardCard::where('user_id', $userId)->where('status', 'In Progress')->count();
        $completedCount = BoardCard::where('user_id', $userId)->where('status', 'Completed')->count();

        // Ambil 3 board terakhir untuk Overview, tapi hanya untuk user yang sedang login
        // di dalam method index() — gantikan bagian recentBoards lama dengan ini
        $recentBoards = BoardCard::where('user_id', $userId)
            ->with(['user', 'members', 'tasks']) // pakai nama relasi yang ada: tasks() dan members()
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($board) {
                // hitung progress berdasarkan tasks() dan kolom is_done pada SubTask
                $total = $board->tasks->count();
                // cek beberapa kemungkinan nama kolom (umumnya is_done)
                $done = $board->tasks->filter(function ($t) {
                    // pastikan aman terhadap null / tipe string/angka
                    if (isset($t->is_done)) {
                        return (bool) $t->is_done;
                    }
                    if (isset($t->is_completed)) {
                        return (bool) $t->is_completed;
                    }
                    // fallback: coba field bernama done
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
                        return ['id' => $m->id, 'name' => $m->name, 'photo' => $m->photo ?? null];
                    })->toArray(),
                    'progress' => $progress,
                ];
            });


        // Ambil 3 user terbaru (ini tidak perlu difilter, karena memang tujuannya menampilkan semua user)
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Ambil semua board yang punya deadline (untuk kalender), tapi hanya untuk user yang sedang login
        $deadlines = BoardCard::where('user_id', $userId)
            ->whereNotNull('deadline')
            ->select('id', 'title', 'deadline')
            ->get();

        // Ambil semua board yang deadlinenya hari ini (untuk Today Schedule), tapi hanya untuk user yang sedang login
        $todayTasks = BoardCard::where('user_id', $userId)
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
