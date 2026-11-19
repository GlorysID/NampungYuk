<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'username', 'email', 'email_verified_at', 'password', 'avatar', 'banner', 'bio', 'github_url', 'website_url', 'reputation_points', 'google_id', 'is_admin', 'is_banned', 'ban_reason', 'banned_at', 'warning_notes', 'warned_at', 'username_changed_at', 'preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'warned_at' => 'datetime',
            'username_changed_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Check if user is currently banned.
     */
    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    /**
     * Check if user currently has an active moderation warning.
     */
    public function hasWarning(): bool
    {
        return ! empty($this->warning_notes);
    }

    /**
     * Check if user can change their username (allowed only once every 7 days).
     */
    public function canChangeUsername(): bool
    {
        if (! $this->username_changed_at) {
            return true;
        }

        return $this->username_changed_at->copy()->addDays(7)->isPast();
    }

    /**
     * Calculate days remaining until next username change is allowed.
     */
    public function daysUntilNextUsernameChange(): int
    {
        if ($this->canChangeUsername()) {
            return 0;
        }

        $nextAllowed = $this->username_changed_at->copy()->addDays(7);

        return (int) max(1, ceil(now()->diffInSeconds($nextAllowed, false) / 86400));
    }

    /**
     * Get the next date when username change is allowed.
     */
    public function nextAllowedUsernameChangeDate(): ?Carbon
    {
        if (! $this->username_changed_at) {
            return null;
        }

        return $this->username_changed_at->copy()->addDays(7);
    }

    /**
     * Reports filed by this user.
     */
    public function reportsSubmitted()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Reports filed against this user.
     */
    public function reportsReceived()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Get normalized banner URL.
     */
    protected function banner(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if (! $value) {
                    return null;
                }

                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    if (preg_match('#/storage/(.+)$#', $value, $matches)) {
                        return asset('storage/'.$matches[1]);
                    }

                    return $value;
                }

                if (str_starts_with($value, '/storage/')) {
                    return asset(ltrim($value, '/'));
                }

                return asset('storage/'.$value);
            }
        );
    }

    /**
     * Get all projects created by user.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all comments by user.
     */
    public function comments()
    {
        return $this->hasMany(ProjectComment::class);
    }

    /**
     * Users that this user follows.
     */
    public function following()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'following_id'
        )->withTimestamps();
    }

    /**
     * Users that follow this user.
     */
    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'follower_id'
        )->withTimestamps();
    }

    /**
     * Whether this user follows the given user.
     */
    public function isFollowing(?User $user): bool
    {
        if (! $user || $this->id === $user->id) {
            return false;
        }

        return $this->following()->whereKey($user->id)->exists();
    }

    /**
     * IDs of users this user follows (for feed filtering).
     *
     * @return array<int, int>
     */
    public function followingIds(): array
    {
        return $this->following()->pluck('users.id')->toArray();
    }

    /**
     * Scope: search accounts by name or username.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%");
        });
    }

    /**
     * Custom profile links (link-in-bio), ordered.
     */
    public function links()
    {
        return $this->hasMany(UserLink::class)->orderBy('sort_order');
    }

    /**
     * Communities this user owns.
     */
    public function ownedCommunities()
    {
        return $this->hasMany(Community::class, 'owner_id');
    }

    /**
     * Communities this user is a member of.
     */
    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Community posts authored by this user.
     */
    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class)->latest();
    }

    /**
     * Conversations this user participates in.
     */
    public function conversations()
    {
        return Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id);
    }

    /**
     * Messages sent by this user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Projects this user has pinned to their profile.
     */
    public function pinnedProjects()
    {
        return $this->hasMany(Project::class)
            ->where('is_pinned', true)
            ->orderByDesc('updated_at');
    }

    /**
     * Projects this user has upvoted (liked).
     */
    public function likedProjects()
    {
        return $this->belongsToMany(
            Project::class,
            'project_votes',
            'user_id',
            'project_id'
        )->wherePivot('type', 'up')->withTimestamps();
    }

    /**
     * Projects this user has reposted.
     */
    public function reposts()
    {
        return $this->hasMany(ProjectRepost::class);
    }

    /**
     * Projects reposted by this user (the original projects).
     */
    public function repostedProjects()
    {
        return $this->belongsToMany(
            Project::class,
            'project_reposts',
            'user_id',
            'project_id'
        )->withTimestamps();
    }

    /**
     * IDs of projects this user has reposted.
     *
     * @return array<int, int>
     */
    public function repostedProjectIds(): array
    {
        return $this->reposts()->pluck('project_id')->toArray();
    }

    /**
     * Users blocked by this account.
     */
    public function blockedUsers()
    {
        return $this->belongsToMany(
            User::class,
            'user_blocks',
            'blocker_id',
            'blocked_id'
        )->withTimestamps();
    }

    /**
     * Users that have blocked this account.
     */
    public function blockedByUsers()
    {
        return $this->belongsToMany(
            User::class,
            'user_blocks',
            'blocked_id',
            'blocker_id'
        )->withTimestamps();
    }

    /**
     * Check whether this user has blocked the given user.
     */
    public function hasBlocked(?User $user): bool
    {
        if (! $user || $this->id === $user->id) {
            return false;
        }

        return $this->blockedUsers()->whereKey($user->id)->exists();
    }

    /**
     * Check whether this user is blocked by the given user.
     */
    public function isBlockedBy(?User $user): bool
    {
        if (! $user || $this->id === $user->id) {
            return false;
        }

        return $user->hasBlocked($this);
    }

    /**
     * Return IDs of all users blocked by this user or blocking this user.
     *
     * @return array<int, int>
     */
    public function allBlockedIds(): array
    {
        $blocked = $this->blockedUsers()->pluck('users.id')->all();
        $blockedBy = $this->blockedByUsers()->pluck('users.id')->all();

        return array_values(array_unique(array_map('intval', array_merge($blocked, $blockedBy))));
    }

    /**
     * Return user preferences merged cleanly with sensible defaults.
     *
     * @return array{
     *     privacy: array{dm_policy: string, allow_reposts: bool, discoverable: bool, show_read_receipts: bool},
     *     notifications: array{new_follower: bool, project_comment: bool, project_repost: bool, new_message: bool, project_uploaded: bool, project_trending: bool},
     *     display: array{autoplay_media: bool, keyboard_shortcuts: bool}
     * }
     */
    public function resolvedPreferences(): array
    {
        $stored = $this->preferences ?? [];

        return [
            'privacy' => array_replace([
                'dm_policy' => 'everyone',
                'allow_reposts' => true,
                'discoverable' => true,
                'show_read_receipts' => true,
            ], is_array($stored['privacy'] ?? null) ? $stored['privacy'] : []),
            'notifications' => array_replace([
                'new_follower' => true,
                'project_comment' => true,
                'project_repost' => true,
                'new_message' => true,
                'project_uploaded' => true,
                'project_trending' => true,
            ], is_array($stored['notifications'] ?? null) ? $stored['notifications'] : []),
            'display' => array_replace([
                'autoplay_media' => true,
                'keyboard_shortcuts' => true,
            ], is_array($stored['display'] ?? null) ? $stored['display'] : []),
        ];
    }

    /**
     * Read a single nested preference key using dot notation.
     */
    public function preference(string $key, mixed $default = null): mixed
    {
        return data_get($this->resolvedPreferences(), $key, $default);
    }

    /**
     * Check whether this user wants a specific notification category.
     */
    public function wantsNotification(string $key): bool
    {
        return (bool) $this->preference("notifications.{$key}", true);
    }

    /**
     * Check whether this user allows direct messages from the given sender.
     * Only blocked relationships are rejected outright; `following_only` routes
     * unfollowed senders into message requests instead of refusing them.
     */
    public function allowsDirectMessageFrom(User $sender): bool
    {
        if ($this->hasBlocked($sender) || $this->isBlockedBy($sender)) {
            return false;
        }

        return true;
    }

    /**
     * Check whether a new message from the given sender must start as a pending
     * message request (permission required) instead of landing directly.
     */
    public function requiresMessageRequestFrom(User $sender): bool
    {
        return $this->preference('privacy.dm_policy', 'everyone') === 'following_only'
            && ! $this->isFollowing($sender);
    }

    /**
     * Check whether others may repost this user's projects.
     */
    public function allowsReposts(): bool
    {
        return (bool) $this->preference('privacy.allow_reposts', true);
    }

    /**
     * Check whether this user allows appearing in public creator directories.
     */
    public function isDiscoverable(): bool
    {
        return (bool) $this->preference('privacy.discoverable', true);
    }

    /**
     * Scope query to users who allow appearing in public creator directories and search.
     */
    public function scopeDiscoverable(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('preferences')
                ->orWhereNull('preferences->privacy->discoverable')
                ->orWhere('preferences->privacy->discoverable', true);
        });
    }
}
