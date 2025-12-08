<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Ambil semua komentar per subtask
    public function index($subtaskId)
    {
        $subtask = SubTask::with('comments.user')->findOrFail($subtaskId);

        return response()->json([
            'comments' => $subtask->comments
                ->sortBy('created_at')
                ->values(),
        ]);
    }

    // Kirim komentar baru
    public function store(Request $request, $subtaskId)
    {
        $request->validate([
            'type' => 'required|string',
            'message' => 'nullable|string',
            'file_path' => 'nullable|string',
        ]);

        $comment = Comment::create([
            'subtask_id' => $subtaskId,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'message' => $request->message,
            'file_path' => $request->file_path,
        ]);

        return response()->json([
            'message' => 'Comment added',
            'comment' => $comment->load('user'),
        ]);
    }

    public function update(Request $request, Comment $comment)
    {
        // 1. Otorisasi (PENTING!)
        // Pastikan hanya pemilik pesan yang bisa mengedit.
        // Asumsi: 'user_id' di tabel comments menyimpan ID pembuat.
        if ($comment->user_id !== auth()->id()) {
            // Mengembalikan respon error 403 Forbidden
            return response()->json(['message' => 'Unauthorized: You are not the owner of this comment.'], 403);
        }

        // 2. Validasi
        $validatedData = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            // Jika Anda mengirim 'edited: true' dari frontend,
            // tidak perlu validasi di sini, cukup validasi data utama.
        ]);

        // 3. Update Data
        $comment->update([
            'message' => $validatedData['message'],
        ]);
        
        // 4. Beri respon sukses
        return response()->json(['message' => 'Comment updated successfully.', 'comment' => $comment], 200);
    }
}
