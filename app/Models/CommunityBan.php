<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityBan extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'banned_by',
        'reason',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
}
