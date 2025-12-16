<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\SubtaskReadStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubTaskController extends Controller
{
    public function show($id)
    {
        $subtask = SubTask::findOrFail($id);

        // AMBIL KOMENTAR LEVEL 1 DAN REPLIES-NYA
        $comments = $subtask->comments()
            ->with([
                'user',
                'parent' => function ($query) {
                    $query->select('id', 'user_id', 'message')->with('user:id,name');
                },
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $lastReadAt = $subtask->readStatus() // (Kamu perlu relasi readStatus di Model SubTask.php)
            ->where('user_id', Auth::id())
            ->value('last_read_at');
            

        
        $card = $subtask->card;

        return inertia('subtask/SubTaskDetail', [
            'subtask' => $subtask->append('first_unread_comment_id'), // <-- tambahkan append
            'card' => $card,
            'collaborators' => $card->collaborators,
            'comments' => $comments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $subtask = SubTask::findOrFail($id);

        if ($subtask->closed_at !== null) {
            return back()->with('error', 'Cannot update a closed subtask.');
        }

        $subtask->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Subtask updated successfully.');
    }

    public function markAsRead(SubTask $subtask)
    {
        // Cari atau buat status baca untuk user yang sedang login di subtask ini
        $status = SubtaskReadStatus::updateOrCreate(
            ['subtask_id' => $subtask->id, 'user_id' => Auth::id()],
            ['last_read_at' => now()] // Update ke waktu sekarang
        
        );

        return response()->json(['success' => true]);
    }

    public function close(SubTask $subtask)
    {
        // 🔥 INI YANG PENTING
        $subtask->update([
            'is_close' => true,   // ← ini yang dipakai frontend
            'is_done'  => true,   // biar checkbox centang
            'closed_at'=> now(),
        ]);

        return back(); // ⬅️ WAJIB supaya Inertia reload
    }

}