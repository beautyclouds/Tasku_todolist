<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardCard;
use App\Models\SubTask;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BordController extends Controller
{
    /**
     * Menampilkan daftar board pribadi dan kolaborasi user yang sedang login.
     */
    public function index()
    {
        $user = Auth::user();

        // My Boards = card buatan user TANPA collaborator
        $myBoards = $user->myBoards()
            ->with(['user', 'collaborators', 'tasks'])
            ->whereNull('closed_at')
            ->whereDoesntHave('collaborators') // card buatan sendiri tapi tidak punya anggota lain
            ->orderBy('created_at', 'desc')
            ->get();

        // Collaboration Boards = card di mana user adalah collaborator
        // atau card buatan user sendiri tapi ada collaborator
        $collaborationBoards = BoardCard::with(['user', 'collaborators', 'tasks'])
            ->whereNull('closed_at')
            ->where(function ($q) use ($user) {
                $q->whereHas('collaborators', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id); // user jadi collaborator
                })
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->whereHas('collaborators'); // user pembuat card + ada collaborator
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('board/Index', [
            'myBoards' => $myBoards,
            'collaborationBoards' => $collaborationBoards,
        ]);
    }


    public function show($id)
    {
        $card = BoardCard::with([
            'tasks.histories' => function ($q) {
                $q->latest()->take(5)->with('user');
            },
            'user',
            'collaborators'
        ])->findOrFail($id);

        $allUsers = User::all();

        return Inertia::render('board/Show', [
            'card' => $card,
            'allUsers' => $allUsers,
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
            'user_id' => Auth::id(), // Tambahkan user_id dari user yang sedang login
        ]);

        return redirect()->route('board.index'); // Diperbaiki: ganti 'board' dengan 'board.index'
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

        return redirect()->route('board.index'); // Diperbaiki: ganti 'board' dengan 'board.index'
    }

    public function destroy($id)
    {
        $card = BoardCard::findOrFail($id);
        $card->delete();

        return redirect()->route('board.index'); // Diperbaiki: ganti 'board' dengan 'board.index'
    }

    public function addTask(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $card = BoardCard::findOrFail($id);
        $card->tasks()->create([
            'name' => $request->name,
            'description' => $request->description,
            'is_done' => false,
        ]);

        $this->updateCardStatus($card);

        return redirect()->route('board.show', $card->id);
    }

    public function toggleTask(Request $request, $id)
    {
        $task = SubTask::findOrFail($id);

        // Tentukan aksi
        $action = $task->is_done ? 'unchecked' : 'checked';

        // Update status
        $task->is_done = !$task->is_done;
        $task->save();

        // Simpan riwayat
        $task->histories()->create([
            'user_id' => Auth::id(),
            'action' => $action,
        ]);

        // Update status card
        $card = $task->card;
        if ($card) {
            $this->updateCardStatus($card);
            return redirect()->route('board.show', $card->id);
        }

        return redirect()->route('board.index')->withErrors('Card not found for this task.');
    }


    private function updateCardStatus(BoardCard $card)
    {
        $total = $card->tasks()->count();
        $completed = $card->tasks()->where('is_done', true)->count();

        if ($total === 0 || $completed === 0) {
            $card->status = 'Pending';
            $card->is_revised = false;
        } elseif ($completed < $total) {
            if ($card->status === 'Completed') {
                $card->is_revised = true;
            }
            $card->status = 'In Progress';
        } else {
            $card->status = 'Completed';
            $card->is_revised = false;
        }

        $card->save();
    }

    public function inviteMember(Request $request, $cardId)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $card = BoardCard::findOrFail($cardId);
        $userToInvite = User::where('email', $request->email)->first();

        if ($userToInvite && $userToInvite->id !== Auth::id()) {
            $card->collaborators()->syncWithoutDetaching([$userToInvite->id]);
            return back()->with('success', 'User invited!');
        }

        return back()->withErrors(['email' => 'User not found or you are trying to invite yourself.']);
    }

    public function updateSubtasks(Request $request, BoardCard $card)
    {
        $subtasks = $request->input('subtasks', []);

        foreach ($subtasks as $s) {
            $task = SubTask::find($s['id']);
            if ($task) {
                $task->is_done = $s['is_completed'];
                $task->save();
            }
        }

        $total = $card->tasks()->count();
        $completed = $card->tasks()->where('is_done', true)->count();

        if ($total === 0 || $completed === 0) {
            $card->status = 'Pending';
            $card->is_revised = false;
        } elseif ($completed < $total) {
            if ($card->status === 'Completed') {
                $card->is_revised = true;
            }
            $card->status = 'In Progress';
        } else {
            $card->status = 'Completed';
            $card->is_revised = false;
        }

        $card->save();

        return response()->json(['status' => 'success', 'card_status' => $card->status]);
    }

    public function close($id)
    {
        $card = BoardCard::findOrFail($id);

        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $card->status = 'archived';
        $card->closed_at = now();
        $card->save();

        return redirect()->route('history.index')->with('success', 'Card berhasil di-close.');
    }

}