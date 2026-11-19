<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityMember extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'role',
        'muted_until',
        'mute_reason',
    ];

    protected function casts(): array
    {
        return [
            'muted_until' => 'datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isMuted(): bool
    {
        return $this->muted_until !== null && $this->muted_until->isFuture();
    }

    public function roleLabel(): string
    {
        return self::labelForRole($this->role);
    }

    public static function labelForRole(?string $role): string
    {
        return match ($role) {
            'owner' => 'Pemilik',
            'admin' => 'Admin',
            'mod' => 'Moderator',
            default => 'Anggota',
        };
    }
}
