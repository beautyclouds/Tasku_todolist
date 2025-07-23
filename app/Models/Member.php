<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BoardCard;

class Member extends Model
{
    protected $fillable = [
        'name',
        'photo',
    ];

    public function boardCards()
    {
        return $this->belongsToMany(BoardCard::class, 'board_card_member');
    }

}
