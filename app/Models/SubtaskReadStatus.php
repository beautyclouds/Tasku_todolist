<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubtaskReadStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtask_id', 
        'user_id', 
        'last_read_at',
    ];

    // Karena kita cuma peduli kapan terakhir dibaca, kita cast ke datetime
    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    // Relasi ke SubTask
    public function subtask()
    {
        return $this->belongsTo(SubTask::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}