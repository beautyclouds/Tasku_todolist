<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\SubTask;
use App\Models\SubtaskHistory;

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

    public function toggleSubtaskIsDone(Request $request, SubTask $subtask)
    {
        // 1. Validasi request dari Vue
        $request->validate([
            'is_done' => ['required', 'boolean'],
            // Comment WAJIB diisi jika kita mau checklist (is_done = true)
            'comment' => ['required_if:is_done,true', 'nullable', 'string', 'min:5', 'max:500'], 
        ]);
        
        $newStatus = $request->is_done;
        
        // Cek apakah statusnya memang berubah dari yang sekarang di database
        if ($subtask->is_done === $newStatus) {
            return response()->json(['message' => 'Status is already set.'], 200);
        }
        
        // 2. Update status subtask
        $subtask->update(['is_done' => $newStatus]);

        // 3. Catat History (termasuk komen)
        $actionType = $newStatus ? 'checked' : 'unchecked';
        $commentToStore = $newStatus ? $request->comment : null;

        SubtaskHistory::create([ 
            'subtask_id' => $subtask->id,
            'user_id' => Auth::id(), // ID user yang sedang login
            'action' => $actionType,
            'comment' => $commentToStore, // Simpan Komen dari Request
        ]);

        return response()->json([
            'message' => 'Subtask status updated and progress recorded successfully.',
            'subtask' => $subtask->refresh()->load('histories.user'), // Load histories terbaru
        ]);
    }
}