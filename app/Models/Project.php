<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'forked_from_id',
        'project_type',
        'status',
        'visibility',
        'title',
        'slug',
        'tagline',
        'description',
        'challenges',
        'learnings',
        'thumbnail',
        'demo_url',
        'github_url',
        'prototype_url',
        'tech_stacks',
        'setup_instructions',
        'upvotes_count',
        'downvotes_count',
        'score',
        'comments_count',
        'views_count',
        'forks_count',
        'is_featured',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'tech_stacks' => 'array',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * Get normalized thumbnail URL.
     */
    protected function thumbnail(): Attribute
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

                if (str_starts_with($value, 'storage/')) {
                    return asset($value);
                }

                return asset('storage/'.$value);
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The project this one was forked from (if any).
     */
    public function forkedFrom(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'forked_from_id');
    }

    /**
     * Projects forked from this project.
     */
    public function forks(): HasMany
    {
        return $this->hasMany(Project::class, 'forked_from_id');
    }

    /**
     * Scope: only projects visible to the given viewer.
     * Public projects are always visible; private ones only to their owner.
     */
    public function scopeVisibleTo(Builder $query, ?int $viewerId): Builder
    {
        return $query->where(function (Builder $q) use ($viewerId) {
            $q->where('visibility', 'public');

            if ($viewerId) {
                $q->orWhere('user_id', $viewerId);
            }
        });
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isFork(): bool
    {
        return ! is_null($this->forked_from_id);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ProjectVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class)->whereNull('parent_id')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(ProjectBookmark::class);
    }

    public function scopeTrending(Builder $query): Builder
    {
        // Trending: prioritize high scores and recent projects
        return $query->orderByDesc('score')->orderByDesc('created_at');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('created_at');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('score')->orderByDesc('upvotes_count');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('tagline', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('tech_stacks', 'like', "%{$term}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%");
                });
        });
    }

    public function getStatusLabel(): ?string
    {
        return match ($this->status) {
            'idea' => 'Ide / Konsep',
            'prototype' => 'Prototipe',
            'beta' => 'Versi Beta',
            'production' => 'Rilis Publik',
            'archived' => 'Arsip',
            default => $this->status ? ucfirst($this->status) : null,
        };
    }

    public function hasKnowledge(): bool
    {
        return ! empty(trim((string) $this->challenges)) || ! empty(trim((string) $this->learnings));
    }

    public function hasSetupInstructions(): bool
    {
        return ! empty(trim((string) $this->setup_instructions));
    }

    public function getUserVoteType(?int $userId = null, ?string $ip = null): ?string
    {
        $query = $this->votes();
        if ($userId) {
            $vote = $query->where('user_id', $userId)->first();
        } else {
            $vote = $query->where('ip_address', $ip)->first();
        }

        return $vote?->type;
    }
}
