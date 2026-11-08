<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'type',
        'content',
        'images',
        'upvotes_count',
        'downvotes_count',
        'score',
        'comments_count',
        'is_pinned',
        'is_answered',
        'answered_comment_id',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_pinned' => 'boolean',
            'is_answered' => 'boolean',
        ];
    }

    /**
     * Post type metadata (label + color).
     */
    public static function types(): array
    {
        return [
            'discussion' => ['label' => 'Diskusi', 'classes' => 'bg-zinc-100 dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 border-zinc-200/80 dark:border-zinc-700/60'],
            'question' => ['label' => 'Tanya Jawab', 'classes' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/50'],
            'announcement' => ['label' => 'Pengumuman', 'classes' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50'],
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type]['label'] ?? 'Diskusi';
    }

    public function typeClasses(): string
    {
        return self::types()[$this->type]['classes'] ?? self::types()['discussion']['classes'];
    }

    public function isQuestion(): bool
    {
        return $this->type === 'question';
    }

    public function scopeSortTab($query, string $tab)
    {
        return match ($tab) {
            'populer' => $query->orderByDesc('score')->orderByDesc('comments_count'),
            'terjawab' => $query->where('is_answered', true)->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityPostComment::class)->whereNull('parent_id')->latest();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommunityPostVote::class);
    }

    public function answeredComment(): BelongsTo
    {
        return $this->belongsTo(CommunityPostComment::class, 'answered_comment_id');
    }

    /**
     * Normalize a stored image path/url into an absolute URL.
     */
    public function imageUrls(): array
    {
        $out = [];
        foreach ((array) ($this->images ?? []) as $img) {
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                $out[] = $img;
            } elseif (str_starts_with($img, '/storage/')) {
                $out[] = asset(ltrim($img, '/'));
            } else {
                $out[] = asset('storage/'.$img);
            }
        }

        return $out;
    }
}
