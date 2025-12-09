<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BoardCard;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; 

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $currentUserId = Auth::id();

        // Ambil input pencarian dari query string (?search=)
        $search = $request->query('search');

        // Ambil semua user kecuali yang sedang login, dan filter jika ada pencarian
        $currentUser = Auth::user();
        $users = User::where('id', '!=', $currentUserId)
        ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($user) use ($currentUser) {

                // ======================================================
                // 🔍 CARI SHARED CARDS (owner + collaborator)
                // ======================================================

                // Card user ini sebagai owner + collaborator
                $theirOwnerCards = $user->myBoards()->pluck('id');     // owner
                $theirCollabCards = $user->cards()->pluck('board_cards.id'); // collaborator
                $theirCards = $theirOwnerCards->merge($theirCollabCards)->unique();

                // Card user yang login (owner + collaborator)
                $myOwnerCards = $currentUser->myBoards()->pluck('id');
                $myCollabCards = $currentUser->cards()->pluck('board_cards.id');
                $myCards = $myOwnerCards->merge($myCollabCards)->unique();

                // Ambil card yang sama-sama dikerjakan
                $shared = BoardCard::whereIn('id', $theirCards)
                    ->whereIn('id', $myCards)
                    ->get()
                    ->map(function ($card) {

                        $total = $card->tasks()->count();
                        $done = $card->tasks()->where('is_done', true)->count();

                        return [
                            'id' => $card->id,
                            'title' => $card->title,
                            'status' => $card->status,
                            'total_subtasks' => $total,
                            'done_subtasks' => $done,
                            'updated_at' => $card->updated_at,  // ← FIX DI SINI
                        ];
                    });

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'shared_cards' => $shared,
                ];
            });

        return Inertia::render('member/Member', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('member.index')->with('success', 'User berhasil dihapus');
    }
}