<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'board_card_id', 'is_done'];

    public function card()
    {
        return $this->belongsTo(BoardCard::class, 'board_card_id');
    }
}
