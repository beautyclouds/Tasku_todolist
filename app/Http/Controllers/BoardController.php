<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardCard;
use App\Models\SubTask;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    /**
     * Menampilkan daftar board pribadi dan kolaborasi user yang sedang login.
     * Mendukung query param `search` untuk filter berdasarkan title (backend search).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        // My Boards = card buatan user TANPA collaborator
        $myBoardsQuery = $user->myBoards()
            ->with([
                'user',
                'collaborators',
                'tasks'])
            ->whereNull('closed_at')
            ->whereDoesntHave('collaborators') // card buatan sendiri tapi tidak punya anggota lain
            ->orderBy('created_at', 'desc');

        if ($search) {
            $myBoardsQuery->where('title', 'like', "%{$search}%");
        }

        $myBoards = $myBoardsQuery->get();

        // Collaboration Boards = card di mana user adalah collaborator
        // atau card buatan user sendiri tapi ada collaborator
        $collaborationBoardsQuery = BoardCard::with(['user', 'collaborators', 'tasks'])
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
            ->orderBy('created_at', 'desc');

        if ($search) {
            $collaborationBoardsQuery->where('title', 'like', "%{$search}%");
        }

        $collaborationBoards = $collaborationBoardsQuery->get();

        return Inertia::render('board/Index', [
            'myBoards' => $myBoards,
            'collaborationBoards' => $collaborationBoards,
            'filters' => [
                'search' => $search,
            ],
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

        // Ambil semua user kecuali owner card
        $allUsers = User::where('id', '!=', $card->user_id)->get();

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
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('board.index');
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

        return redirect()->route('board.index');
    }

    public function destroy($id)
    {
        $card = BoardCard::findOrFail($id);
        $card->delete();

        return redirect()->route('board.index');
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

    /**
     * Update subtasks dari payload: subtasks: [{ id: .., is_completed: true/false }, ...]
     */
    public function updateSubtasks(Request $request, BoardCard $card)
    {
        $subtasks = $request->input('subtasks', []);

        foreach ($subtasks as $s) {
            $task = SubTask::find($s['id'] ?? null);
            if ($task) {
                $task->is_done = !empty($s['is_completed']);
                $task->save();
            }
        }

        $this->updateCardStatus($card);

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

    public function removeMember($cardId, $userId)
    {
        $card = BoardCard::findOrFail($cardId);

        // Hanya owner yang bisa keluarkan member
        if ($card->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $card->collaborators()->detach($userId);

        return back()->with('success', 'Member removed!');
    }

    public function leaveCard($cardId)
    {
        $card = BoardCard::findOrFail($cardId);

        // Hanya berlaku untuk user biasa (collaborator)
        $card->collaborators()->detach(Auth::id());

        return redirect()->route('board.index')->with('success', 'You left the card.');
    }

    public function deleteTask($taskId)
    {
        $task = SubTask::findOrFail($taskId);
        $cardId = $task->board_card_id; // simpan dulu id card sebelum dihapus
        $task->delete();

        // Redirect ke halaman detail card
        return redirect()->route('board.show', $cardId)
                         ->with('success', 'Subtask berhasil dihapus!');
    }

}