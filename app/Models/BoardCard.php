<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Member;

class BoardCard extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'deadline', 'priority', 'status'];

    public function tasks()
    {
        return $this->hasMany(SubTask::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'board_card_member');
    }

}
