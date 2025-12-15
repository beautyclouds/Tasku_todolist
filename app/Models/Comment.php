<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtask_id',
        'user_id',
        'type',
        'message',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'parent_id',
    ];

    public function subtask()
    {
        return $this->belongsTo(SubTask::class, 'subtask_id'); // 🔥 tambahkan FK yang benar
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

}