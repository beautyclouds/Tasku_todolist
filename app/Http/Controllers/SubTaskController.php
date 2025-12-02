<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function show($id)
    {
        // 🟢 Load card + user (owner) + collaborators
        $subtask = SubTask::with([
            'card.user',          // load owner
            'card.collaborators'  // load collaborators
        ])->findOrFail($id);

        $card = $subtask->card;

        return inertia('subtask/SubTaskDetail', [
            'subtask' => $subtask,
            'card' => $card,
            'collaborators' => $card->collaborators, // sudah otomatis ke-load
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