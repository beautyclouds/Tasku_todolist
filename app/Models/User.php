<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke board yang dia buat.
     */
    public function myBoards(): HasMany
    {
        return $this->hasMany(BoardCard::class, 'user_id');
    }

    /**
     * Relasi ke board di mana dia diundang sebagai kolaborator.
     */
    public function collaborationBoards(): BelongsToMany
    {
        return $this->belongsToMany(BoardCard::class, 'board_collaborator', 'user_id', 'board_card_id');
    }
}
