<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubtaskHistory extends Model
{
    use HasFactory;

    protected $fillable = ['subtask_id', 'user_id', 'action'];

    public function subtask()
    {
        return $this->belongsTo(SubTask::class, 'subtask_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}