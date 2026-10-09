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
    ];

    protected function casts(): array
    {
        return [
            'is_muted' => 'boolean',
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
}
