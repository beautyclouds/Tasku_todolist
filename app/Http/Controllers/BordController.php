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
     * Logika ini menggabungkan kode dari kedua file untuk memastikan semua board terambil dengan benar.
     */
    public function index()
    {
        $user = Auth::user();

        // My Boards: card yang dibuat oleh user DAN TIDAK memiliki kolaborator.
        $myBoards = $user->myBoards()
            ->with(['user', 'collaborators', 'tasks']) // Eager loading untuk relasi
            ->whereNull('closed_at')
            ->whereDoesntHave('collaborators') // Hanya board tanpa kolaborator
            ->orderBy('created_at', 'desc')
            ->get();

        // Collaboration Boards: board di mana user adalah kolaborator,
        // ATAU board yang dibuat oleh user DAN memiliki kolaborator.
        $collaborationBoards = BoardCard::with(['user', 'collaborators', 'tasks'])
            ->whereNull('closed_at')
            ->where(function ($q) use ($user) {
                // Kondisi 1: Board di mana user_id di tabel `board_card_user` adalah user yang sedang login
                $q->whereHas('collaborators', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                })
                // Kondisi 2: Board yang dibuat oleh user sendiri TAPI memiliki kolaborator
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                       ->whereHas('collaborators');
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
        // Menampilkan detail satu card dengan relasi terkait
        $card = BoardCard::with(['tasks', 'user', 'collaborators'])->findOrFail($id);
        $allUsers = User::all(); // Mengambil semua user untuk fitur invite

        return Inertia::render('board/Show', [
            'card' => $card,
            'allUsers' => $allUsers,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi dan menyimpan card baru
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
        // Memperbarui detail card
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
        // Menghapus card
        $card = BoardCard::findOrFail($id);
        $card->delete();

        return redirect()->route('board.index');
    }

    public function addTask(Request $request, $id)
    {
        // Menambahkan sub-task ke card
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
        // Mengubah status `is_done` dari sub-task
        $task = SubTask::findOrFail($id);
        $task->is_done = !$task->is_done;
        $task->save();

        $card = $task->card;
        if ($card) {
            $this->updateCardStatus($card);
            return redirect()->route('board.show', $card->id);
        }

        return redirect()->route('board.index')->withErrors('Card not found for this task.');
    }

    private function updateCardStatus(BoardCard $card)
    {
        // Memperbarui status card berdasarkan sub-task yang sudah selesai
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
        // Mengundang user sebagai kolaborator
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
        // Memperbarui status beberapa sub-task sekaligus dari request
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
        // Menutup board secara permanen
        $card = BoardCard::findOrFail($id);

        $card->status = 'Completed';
        $card->closed_at = now();
        $card->save();

        return back()->with('success', 'Card berhasil di-close.');
    }
}
