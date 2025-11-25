<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function show($id)
    {
        $subtask = SubTask::with('card')->findOrFail($id);

        $card = $subtask->card;

        return inertia('subtask/SubTaskDetail', [
            'subtask' => $subtask,
            'card' => $card,
            'collaborators' => $card->members()->select('users.*')->get(),
        ]);
    }


}
