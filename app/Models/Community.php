<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'visibility',
        'icon',
        'cover',
        'members_count',
        'posts_count',
        'is_featured',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)->latest();
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function memberUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'community_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    /**
     * Whether the given user is a member of this community.
     */
    public function hasMember(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Whether the given user is the owner or a moderator.
     */
    public function isModerator(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        if ($this->owner_id === $userId) {
            return true;
        }

        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'mod'])
            ->exists();
    }

    public function scopeVisibleTo($query, ?int $viewerId)
    {
        return $query->where(function ($q) use ($viewerId) {
            $q->where('visibility', 'public');

            if ($viewerId) {
                $q->orWhere('owner_id', $viewerId)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $viewerId));
            }
        });
    }
}
