<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function show($id)
    {
        $subtask = SubTask::findOrFail($id);

        // AMBIL KOMENTAR LEVEL 1 DAN REPLIES-NYA
        $comments = $subtask->comments()
            ->with([
                'user',
                'replies.user',     // 🔥 ambil user yang reply
                'replies.replies',  // 🔥 kalau mau support nested reply level 2+
            ])
            ->whereNull('parent_id') // 🔥 komentar utama saja
            ->orderBy('created_at', 'asc')
            ->get();

        $card = $subtask->card;

        return inertia('subtask/SubTaskDetail', [
            'subtask' => $subtask,
            'card' => $card,
            'collaborators' => $card->collaborators,
            'comments' => $comments,   // 🔥 ini komentar yang sudah sesuai reply
        ]);
    }

    public function update(Request $request, $id)
    {
        $subtask = SubTask::findOrFail($id);

        $subtask->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Subtask updated successfully.');
    }

}