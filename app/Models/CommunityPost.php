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
            'discussion' => ['label' => 'Diskusi', 'classes' => 'bg-[#e8f2ff] dark:bg-[#3291ff]/15 text-[#0070f3] dark:text-[#47a8ff] border-[#3291ff]/25'],
            'question' => ['label' => 'Tanya Jawab', 'classes' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/50'],
            'announcement' => ['label' => 'Pengumuman', 'classes' => 'bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-300 border-violet-200 dark:border-violet-800/50'],
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
