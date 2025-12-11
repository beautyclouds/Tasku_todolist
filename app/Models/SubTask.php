<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_done',
        'card_id',
        'is_close',
    ];

    public function card()
    {
        return $this->belongsTo(BoardCard::class, 'board_card_id'); // pastikan nama foreign key benar
    }

    public function histories()
    {
        return $this->hasMany(\App\Models\SubtaskHistory::class, 'subtask_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'subtask_id');  // 🔥 tambahkan FK yang benar
    }


}
