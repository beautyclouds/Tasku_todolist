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
    ];

    public function card()
    {
        return $this->belongsTo(BoardCard::class, 'board_card_id'); // pastikan nama foreign key benar
    }

    public function histories()
    {
        return $this->hasMany(\App\Models\SubtaskHistory::class, 'subtask_id');
    }

}
