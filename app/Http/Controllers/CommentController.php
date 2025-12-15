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
        // Ambil semua komentar di subtask ini, diurutkan.
        // PENTING: Eager load 'parent' dan 'parent.user'
        $comments = Comment::where('subtask_id', $subtaskId)
            ->with([
                'user',
                'parent' => function ($query) {
                    $query->select('id', 'user_id', 'message')->with('user:id,name');
                },
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }

    // Kirim komentar baru
    public function store(Request $request, $subtaskId)
    {
        // Validasi
        $request->validate([
            'type' => 'required|string',
            'message' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'file' => 'nullable|file|max:40960', // 40MB
        ]);

        $filePath = null;
        $fileName = null;
        $fileType = null;
        $fileSize = null;

        // Jika ada FILE yang dikirim
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Simpan ke storage/app/public/comments
            $filePath = $file->store('comments', 'public');

            // Ambil detail file
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getClientMimeType();
            $fileSize = $file->getSize();
        }

        // Simpan komentar ke database
        $comment = Comment::create([
            'subtask_id' => $subtaskId,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'message' => $request->message,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'message' => 'Comment added',
            'comment' => $comment->load('user', 'parent'),
        ]);
    }



    public function update(Request $request, Comment $comment)
    {
        // 1. Otorisasi (PENTING!)
        // Pastikan hanya pemilik pesan yang bisa mengedit.
        // Asumsi: 'user_id' di tabel comments menyimpan ID pembuat.
        if ($comment->user_id !== Auth::id()) {
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

    public function destroy(Comment $comment)
    {
        // 1. Otorisasi (PENTING!)
        // Pastikan user yang login adalah pemilik pesan.
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized: You are not the owner of this comment.'], 403);
        }

        // 2. Hapus Komentar
        $comment->delete();
        
        // 3. Beri respon sukses
        return response()->json(['message' => 'Comment deleted successfully.'], 200);
    }
}