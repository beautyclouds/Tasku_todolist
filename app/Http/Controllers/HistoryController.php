<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use App\Models\User; // Ganti 'Member' dengan 'User'
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class HistoryController extends Controller
{
    // Halaman list history
    public function index()
    {
        // Load tasks dan user
        $cards = BoardCard::with(['tasks', 'user:id,name', 'collaborators']) // Ganti 'members' dengan 'user' dan 'collaborators'
            ->whereNotNull('closed_at') // hanya yang sudah di-close
            ->get();

        return Inertia::render('history/HistoryPage', [
            'cards' => $cards,
        ]);
    }

    // Halaman detail history
    public function show($id)
    {
        $card = BoardCard::with(['tasks', 'user', 'collaborators'])->findOrFail($id); // Ganti 'members' dengan 'user' dan 'collaborators'

        return Inertia::render('history/HistoryDetail', [
            'card' => $card,
        ]);
    }

    // Close card
    public function close($id)
    {
        $card = BoardCard::findOrFail($id);
        $card->status = 'archived';
        $card->save();

        return redirect()->route('history.index')->with('success', 'Card berhasil di-close.');
    }
}
