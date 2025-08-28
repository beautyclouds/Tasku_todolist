<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada

class HistoryController extends Controller
{
    // Halaman list history
    public function index()
    {
        // Ambil ID user yang sedang login
        $userId = Auth::id();

        // Load cards berdasarkan user yang sedang login dan yang sudah di-close
        $cards = BoardCard::with(['tasks', 'user:id,name', 'collaborators'])
            ->whereNotNull('closed_at')
            ->where('user_id', $userId) // Tambahkan filter ini
            ->get();

        return Inertia::render('history/HistoryPage', [
            'cards' => $cards,
        ]);
    }

    // Halaman detail history
    public function show($id)
    {
        $card = BoardCard::with(['tasks', 'user', 'collaborators'])->findOrFail($id);

        return Inertia::render('history/HistoryDetail', [
            'card' => $card,
        ]);
    }

    // Close card
    public function close($id)
    {
        $card = BoardCard::findOrFail($id);
        $card->status = 'archived';
        $card->closed_at = now(); // Pastikan timestamp closed_at diisi
        $card->save();

        return redirect()->route('history.index')->with('success', 'Card berhasil di-close.');
    }
}
