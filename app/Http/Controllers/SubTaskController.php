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

        $lastcomment = $subtask->comments()->latest()->first();
        $lastcommentid = $lastcomment ? $lastcomment->id : null;

        // Tandai sudah dibaca (Upsert: Update jika ada, Create jika tidak ada)
        SubtaskReadStatus::updateOrCreate(
            [
                'subtask_id' => $id,
                'user_id' => Auth::id(),
            ],
            [
                'last_read_at' => now(),
                'last_comment_id' => $lastcommentid, // Opsional: simpan ID komentar terakhir
            ]
        );

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
        // 1. Cek status: Kalau closed_at sudah ada isinya, balikin error
        if ($subtask->closed_at !== null) {
        return response()->json(['message' => 'Subtask already closed.'], 400);
        }
    
        // 2. Update kolom closed_at
        $subtask->update([
        'closed_at' => now(), // Set ke waktu saat ini
        ]);
    
        // 3. Muat ulang data (penting jika ada appends/relasi lain yang perlu di-update)
        $subtask->load('card.collaborators'); // Muat ulang relasi yang dibutuhkan Vue
    
        return response()->json([
        'message' => 'Subtask closed successfully.',
        'subtask' => $subtask, // Kirim data subtask yang sudah di-close kembali ke Vue
        ]);
    }
}