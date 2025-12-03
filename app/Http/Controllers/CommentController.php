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
}
