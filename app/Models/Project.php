<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'title',
        'slug',
        'tagline',
        'description',
        'thumbnail',
        'demo_url',
        'github_url',
        'prototype_url',
        'tech_stacks',
        'upvotes_count',
        'downvotes_count',
        'score',
        'comments_count',
        'views_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'tech_stacks' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
                ->orWhere('tech_stacks', 'like', "%{$term}%");
        });
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
