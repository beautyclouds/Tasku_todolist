<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

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
     * Relasi ke board yang dia buat (owner)
     */
    public function myBoards(): HasMany
    {
        return $this->hasMany(BoardCard::class, 'user_id');
    }

    /**
     * Relasi ke board di mana dia diundang sebagai kolaborator
     */
    public function collaborationBoards(): BelongsToMany
    {
        return $this->belongsToMany(BoardCard::class, 'board_collaborator', 'user_id', 'board_card_id');
    }

    /**
     * Relasi untuk mengecek status aktif (hanya jika user kolaborator)
     */
    public function cards(): BelongsToMany
    {
        return $this->collaborationBoards();
    }

    /**
    * Accessor untuk status user
    */
    public function getStatusAttribute()
    {
        // User kolaborator di board aktif
        $activeCollab = $this->collaborationBoards()->whereNull('closed_at')->exists();

        // User owner dari board yang punya kolaborator aktif
        $activeOwner = $this->myBoards()
            ->whereNull('closed_at')
            ->whereHas('collaborators') // pastikan board itu punya kolaborator
            ->exists();

        return ($activeCollab || $activeOwner) ? 'Aktif' : 'Tidak Aktif';
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function boards()
    {
        return $this->belongsToMany(BoardCard::class, 'board_collaborator', 'user_id', 'board_card_id');
    }
}