<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    // ... (property $rootView, function version) ...

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // Ambil User yang sedang login
        $user = $request->user(); // Gunakan $user ini di bawah

        // Definisi array yang akan di-return
        return [
            ...parent::share($request),

            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],

            'auth' => [
                'user' => $user, // Menggunakan $user yang sudah diambil
            ],

            'unread_count' => $user
    ? \App\Models\Comment::where('is_read', 1)
        // Jangan itung pesan yang kita ketik sendiri
        ->where('user_id', '!=', $user->id)
        // Cek lewat relasi subtask -> card
        ->whereHas('subtask.card', function ($query) use ($user) {
            $query->where('user_id', $user->id) // Kita ownernya
                ->orWhereHas('collaborators', function ($q) use ($user) { // Atau kita anggotanya
                    $q->where('user_id', $user->id);
                });
        })
        ->count()
    : 0,

            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];

        // --- HILANGKAN SEMUA KODE YANG ADA DI BAWAH INI DARI KODE ASLI KAMU ---
        // Karena ini adalah return yang benar, semua kode di antara dua return statement harus digabung ke array return ini.
    }
}
