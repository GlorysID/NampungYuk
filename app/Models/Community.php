<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    protected $attributes = [
        'visibility' => 'public',
        'join_policy' => 'open',
        'post_policy' => 'all_members',
        'allow_member_comments' => true,
        'allow_member_images' => true,
    ];

    protected $fillable = [
        'owner_id',
        'name',
        'topic',
        'location',
        'website_url',
        'slug',
        'description',
        'rules',
        'welcome_message',
        'join_questions',
        'rules_items',
        'visibility',
        'join_policy',
        'post_policy',
        'allow_member_comments',
        'allow_member_images',
        'icon',
        'cover',
        'members_count',
        'posts_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'allow_member_comments' => 'boolean',
            'allow_member_images' => 'boolean',
            'is_featured' => 'boolean',
            'join_questions' => 'array',
            'rules_items' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)->latest();
    }

    public function approvedPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)->approved()->latest();
    }

    public function pendingPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)->pending()->latest();
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function memberUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'community_members')
            ->withPivot(['role', 'muted_until', 'mute_reason'])
            ->withTimestamps();
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(CommunityJoinRequest::class);
    }

    public function pendingJoinRequests(): HasMany
    {
        return $this->hasMany(CommunityJoinRequest::class)->where('status', 'pending');
    }

    public function bans(): HasMany
    {
        return $this->hasMany(CommunityBan::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CommunityAuditLog::class);
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function memberRecord(?int $userId): ?CommunityMember
    {
        if (! $userId) {
            return null;
        }

        return $this->members()->where('user_id', $userId)->first();
    }

    public function roleOf(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        if ((int) $this->owner_id === (int) $userId) {
            return 'owner';
        }

        return $this->memberRecord($userId)?->role;
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

    public function isOwner(?int $userId): bool
    {
        return $userId !== null && (int) $this->owner_id === (int) $userId;
    }

    /**
     * Owner or community admin (full settings & member governance).
     */
    public function isAdmin(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        if ($this->isOwner($userId)) {
            return true;
        }

        return in_array($this->roleOf($userId), ['owner', 'admin'], true);
    }

    /**
     * Whether the given user is the owner, admin, or moderator.
     */
    public function isModerator(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        if ($this->isOwner($userId)) {
            return true;
        }

        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin', 'mod'])
            ->exists();
    }

    public function isBanned(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->bans()->where('user_id', $userId)->exists();
    }

    public function isMuted(?int $userId): bool
    {
        return $this->memberRecord($userId)?->isMuted() ?? false;
    }

    public function requiresJoinApproval(): bool
    {
        return $this->join_policy === 'approval' || $this->isPrivate();
    }

    public function canPost(?int $userId): bool
    {
        if (! $userId || ! $this->hasMember($userId) || $this->isBanned($userId) || $this->isMuted($userId)) {
            return false;
        }

        if ($this->post_policy === 'admins_only') {
            return $this->isModerator($userId);
        }

        return true;
    }

    public function postsRequireApproval(?int $userId): bool
    {
        return $this->post_policy === 'approval_required' && ! $this->isModerator($userId);
    }

    public function canManageRole(int $actorId, string $targetCurrentRole, ?string $newRole = null): bool
    {
        if ($targetCurrentRole === 'owner') {
            return false;
        }

        if ($this->isOwner($actorId)) {
            return $newRole === null ? true : in_array($newRole, ['admin', 'mod', 'member'], true);
        }

        if ($this->isAdmin($actorId)) {
            if ($targetCurrentRole === 'admin') {
                return false;
            }

            return $newRole === null ? true : in_array($newRole, ['mod', 'member'], true);
        }

        return false;
    }

    /**
     * Structured community rules: prefers rules_items JSON, falling back to legacy rules text.
     *
     * @return array<int, array{title: string, description: ?string}>
     */
    public function structuredRules(): array
    {
        $items = [];
        foreach ((array) ($this->rules_items ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $desc = trim((string) ($item['description'] ?? ''));
            $items[] = [
                'title' => $title,
                'description' => $desc !== '' ? $desc : null,
            ];
        }

        if (! empty($items)) {
            return $items;
        }

        $legacy = trim((string) ($this->rules ?? ''));
        if ($legacy === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $legacy) ?: [];
        foreach ($lines as $line) {
            $clean = trim(preg_replace('/^\d+[\.\)]\s*/', '', $line) ?? $line);
            if ($clean !== '') {
                $items[] = ['title' => $clean, 'description' => null];
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordAudit(?int $actorId, string $action, string $summary, ?int $targetUserId = null, array $meta = []): CommunityAuditLog
    {
        return $this->auditLogs()->create([
            'actor_id' => $actorId,
            'action' => $action,
            'target_user_id' => $targetUserId,
            'summary' => mb_strimwidth($summary, 0, 250, '…'),
            'meta' => ! empty($meta) ? $meta : null,
            'created_at' => now(),
        ]);
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

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('topic', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%");
        });
    }
}
