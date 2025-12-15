<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\User;
use Illuminate\Support\Arr; // Tambahkan ini jika kamu butuh fungsi array utility, tapi di sini belum dipakai.

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
            
            // --- START: LOGIC NOTIFIKASI DITAMBAH (SUDAH BENAR) ---
            'unreadCount' => fn () => $user
                ? $user->unreadNotifications()->count()
                : 0,

            'recentNotifications' => fn () => $user
                ? $user->notifications()
                    ->take(5) // Ambil 5 notifikasi terbaru
                    ->get()
                    ->map(function ($notification) {
                        return [
                            'id' => $notification->id,
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at,
                            'data' => $notification->data, // Ini adalah payload JSON yang kita butuhkan
                        ];
                    })
                : [],
            // --- END: LOGIC NOTIFIKASI DITAMBAH ---

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