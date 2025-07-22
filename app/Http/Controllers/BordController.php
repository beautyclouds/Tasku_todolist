<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardCard;
use App\Models\SubTask;
use Inertia\Inertia;

class BordController extends Controller
{
    public function index()
    {
        $cards = BoardCard::orderBy('created_at', 'desc')->get();

        return Inertia::render('board/Index', [
            'cards' => $cards,
        ]);
    }

    public function show($id)
    {
        $card = BoardCard::with('tasks')->findOrFail($id);

        return Inertia::render('board/Show', [
            'card' => $card,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:Low,Normal,High',
        ]);

        BoardCard::create([
            'title' => $request->title,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'status' => 'Pending', // Default status
        ]);

        return redirect()->route('board');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date',
            'priority' => 'required|in:Low,Normal,High',
        ]);

        $card = BoardCard::findOrFail($id);
        $card->update($request->only('title', 'deadline', 'priority'));

        return redirect()->route('board');
    }

    public function destroy($id)
    {
        $card = BoardCard::findOrFail($id);
        $card->delete();

        return redirect()->route('board');
    }

    public function addTask(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $card = BoardCard::findOrFail($id);

        $card->tasks()->create([
            'name' => $request->name,
        ]);

        $this->updateCardStatus($card);

        return redirect()->route('board.show', $card->id);
    }

    public function toggleTask(Request $request, $id)
    {
        $task = SubTask::findOrFail($id);
        $task->is_done = !$task->is_done;
        $task->save();

        $card = $task->card;
        $this->updateCardStatus($card);

        return redirect()->route('board.show', $card->id);
    }

    private function updateCardStatus(BoardCard $card)
    {
        $total = $card->tasks()->count();
        $completed = $card->tasks()->where('is_done', true)->count();

        if ($total === 0) {
            $card->status = 'Pending';
        } elseif ($completed === 0) {
            $card->status = 'Pending';
        } elseif ($completed < $total) {
            $card->status = 'In Progress';
        } elseif ($completed === $total) {
            $card->status = 'Completed';
        }

        $card->save();
    }
}
