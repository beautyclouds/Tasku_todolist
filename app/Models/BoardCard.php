<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoardCard extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'deadline', 'priority', 'status'];

    public function tasks()
    {
        return $this->hasMany(SubTask::class);
    }
}
