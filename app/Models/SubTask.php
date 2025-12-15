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
        return $this->hasOne(SubtaskReadStatus::class, 'subtask_id');
    }

    public function getUnreadCommentsCountAttribute()
    {
        // 1. Ambil status baca user yang sedang login
        $readStatus = $this->readStatus()
                           ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                           ->first();
        
        // 2. Tentukan waktu terakhir dibaca (jika belum pernah dibaca, anggap null)
        $lastReadAt = $readStatus ? $readStatus->last_read_at : null;

        // 3. Hitung komentar yang dibuat SETELAH waktu terakhir dibaca
        // Jika lastReadAt null, kita hitung semua komentar
        $query = $this->comments();

        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->count();
    }
}