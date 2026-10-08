<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'avatar', 'bio', 'github_url', 'reputation_points', 'google_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
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
        ];
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
}
