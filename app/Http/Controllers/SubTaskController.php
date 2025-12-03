<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function show($id)
    {
        $subtask = SubTask::with([
            'card.user',
            'card.collaborators',
            'comments.user', // 🔥 Tambahin ini untuk load komentar
        ])->findOrFail($id);

        $card = $subtask->card;

        return inertia('subtask/SubTaskDetail', [
            'subtask' => $subtask,
            'card' => $card,
            'collaborators' => $card->collaborators,
            'comments' => $subtask->comments,   // 🔥 WAJIB ADA!
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