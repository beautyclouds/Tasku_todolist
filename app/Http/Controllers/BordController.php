<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardCard;
use App\Models\Member;
use App\Models\SubTask;
use Inertia\Inertia;

class BordController extends Controller
{
    public function index()
    {
        $cards = BoardCard::with('members')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('board/Index', [
            'cards' => $cards,
        ]);
    }

    public function show($id)
    {
        $card = BoardCard::with(['tasks', 'members'])->findOrFail($id);
        $allMembers = Member::all();

        return Inertia::render('board/Show', [
            'card' => $card,
            'allMembers' => $allMembers,
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
            'status' => 'Pending',
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
        $card->update([
            'title' => $request->title,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
        ]);

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
            'is_done' => false,
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

        if ($total === 0 || $completed === 0) {
            $card->status = 'Pending';
        } elseif ($completed < $total) {
            $card->status = 'In Progress';
        } else {
            $card->status = 'Completed';
        }

        $card->save();
    }

    public function inviteMember(Request $request, $cardId)
    {
        $request->validate([
            'name' => 'required|string|exists:members,name',
        ]);

        $card = BoardCard::findOrFail($cardId);
        $member = Member::where('name', $request->name)->first();

        if (!$member) {
            return back()->withErrors(['name' => 'Member not found.']);
        }

        $card->members()->syncWithoutDetaching([$member->id]);

        return back()->with('success', 'Member invited!');
    }
}
