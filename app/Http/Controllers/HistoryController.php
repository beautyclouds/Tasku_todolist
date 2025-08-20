<?php

namespace App\Http\Controllers;

use App\Models\BoardCard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class HistoryController extends Controller
{
    // Halaman list history
    public function index()
    {
        // Load tasks dan members
        $cards = BoardCard::with(['tasks', 'members:id,name,photo'])
            ->whereNotNull('closed_at') // hanya yang sudah di-close
            ->get()
            ->map(function ($card) {
                // Transform member photo agar bisa diakses
                $card->members->transform(function ($member) {
                    $member->photo = $member->photo ? Storage::url($member->photo) : null;
                    return $member;
                });
                return $card;
            });

        return Inertia::render('history/HistoryPage', [
            'cards' => $cards,
        ]);
    }

    // Halaman detail history
    public function show($id)
    {
        $card = BoardCard::with(['tasks', 'members:id,name,photo'])->findOrFail($id);

        // Transform member photo
        $card->members->transform(function ($member) {
            $member->photo = $member->photo ? Storage::url($member->photo) : null;
            return $member;
        });

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
