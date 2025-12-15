<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller; // Pastikan ini ada
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua notifikasi dan tandai yang belum dibaca sebagai sudah dibaca
        $notifications = $request->user()
            ->notifications()
            ->paginate(20);
            
        $request->user()->unreadNotifications->markAsRead(); // Tandai semua sebagai dibaca

        return Inertia::render('Notifications/NotificationPage', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        // 1. Otorisasi: Pastikan notifikasi ini milik user yang sedang login
        if ($request->user()->id !== $notification->notifiable_id) {
            // Jika bukan, tolak request
            return response()->json(['message' => 'Unauthorized access'], 403);
        }
        
        // 2. Tandai sebagai dibaca
        $notification->markAsRead();

        // 3. Beri respon sukses
        return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
    }


    /**
     * Tandai SEMUA notifikasi yang belum dibaca sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        // Setelah sukses, kita redirect atau kirim respon kosong/sukses
        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }
}