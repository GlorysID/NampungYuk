<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceParticipant extends Model
{
    protected $fillable = [
        'space_id',
        'user_id',
        'role',
        'is_muted',
        'hand_raised_at',
        'is_forced_muted',
    ];

    protected function casts(): array
    {
        return [
            'is_muted' => 'boolean',
            'is_forced_muted' => 'boolean',
            'hand_raised_at' => 'datetime',
        ];
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOnStage(): bool
    {
        return in_array($this->role, Space::STAGE_ROLES, true);
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['host', 'cohost'], true);
    }
}
