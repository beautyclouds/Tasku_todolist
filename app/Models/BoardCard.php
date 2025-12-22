<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BoardCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'deadline',
        'priority',
        'status',
        'closed_at',
        'user_id', // Tambahkan user_id di sini
    ];

    protected $dates = ['deadline', 'closed_at'];

    // Relasi ke SubTask
    public function tasks()
    {
        return $this->hasMany(SubTask::class, 'board_card_id');
    }

    /**
     * Relasi ke user pembuat (board ini dimiliki oleh 1 user).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke user-user kolaborator (1 board bisa punya banyak kolaborator).
     */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_collaborator', 'board_card_id', 'user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

}