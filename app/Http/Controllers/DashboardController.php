<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung jumlah Board berdasarkan status
        $pendingCount = BoardCard::where('status', 'Pending')->count();
        $inProgressCount = BoardCard::where('status', 'In Progress')->count();
        $completedCount = BoardCard::where('status', 'Completed')->count();

        // Ambil 3 board terakhir untuk Overview
        $recentBoards = BoardCard::with('members')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Ambil 3 member terbaru
        $recentMembers = Member::orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Ambil semua board yang punya deadline (untuk kalender)
        $deadlines = BoardCard::whereNotNull('deadline')
            ->select('id', 'title', 'deadline')
            ->get();

        // Ambil semua board yang deadlinenya hari ini (untuk Today Schedule)
        $todayTasks = BoardCard::whereDate('deadline', now())
            ->select('id', 'title', 'deadline')
            ->get();

        return Inertia::render('Dashboard', [
            'pendingCount' => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'recentBoards' => $recentBoards,
            'recentMembers' => $recentMembers,
            'deadlines' => $deadlines,
            'todayTasks' => $todayTasks,
        ]);
    }
}
