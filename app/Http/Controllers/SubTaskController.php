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
                'parent' => function ($query) {
                    $query->select('id', 'user_id', 'message')->with('user:id,name');
                },
            ])
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

    public function close(SubTask $subtask)
    {
        $subtask->update([
            'is_close' => true,
        ]);

        // Redirect ke halaman detail card
        return redirect()->route('board.show', $subtask->board_card_id)
                         ->with('success', 'Subtask berhasil ditutup!');
    }


}