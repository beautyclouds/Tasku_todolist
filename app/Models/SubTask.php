<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubtaskReadStatus;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_done',
        'board_card_id', // 🔥 UBAH DARI 'card_id' JADI 'board_card_id'
        'closed_at',     // 🔥 TAMBAHKAN KOLOM BARU INI
        'card_id',
        'is_close',
    ];
    
    // 🔥 Wajib tambahkan casts, karena closed_at itu timestamp (Carbon instance)
    protected $casts = [
        'is_done' => 'boolean',
        'closed_at' => 'datetime',
    ];

    protected $appends = ['unread_comments_count'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function card()
    {
        return $this->belongsTo(BoardCard::class, 'board_card_id');
    }

    public function histories()
    {
        return $this->hasMany(\App\Models\SubtaskHistory::class, 'subtask_id');
    }

    public function comments()
    {
        // Asumsi FK di tabel comments adalah 'subtask_id'
        return $this->hasMany(Comment::class, 'subtask_id');
    }

    public function readStatus()
    {
        return $this->hasMany(SubtaskReadStatus::class, 'subtask_id'); // ❌ sebelumnya hasOne
    }

    public function getUnreadCommentsCountAttribute()
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
    
        $readStatus = $this->readStatus()->where('user_id', $userId)->first();
        $lastReadAt = $readStatus ? $readStatus->last_read_at : null;

        $query = $this->comments()->where('user_id', '!=', $userId); // ❌ hanya komentar orang lain

        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->count();
    }

    // Ambil komentar pertama yang belum dibaca oleh user
    public function getFirstUnreadCommentIdAttribute()
    {
        $userId = auth()->id();

        // Ambil waktu terakhir user membaca subtask
        $lastReadAt = $this->readStatus()->where('user_id', $userId)->value('last_read_at');

        $query = $this->comments()->where('user_id', '!=', $userId);

        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->orderBy('created_at', 'asc')->value('id');
    }

}