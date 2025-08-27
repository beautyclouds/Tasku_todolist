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
        $recentBoards = BoardCard::where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

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
