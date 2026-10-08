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
        'content',
        'images',
        'upvotes_count',
        'downvotes_count',
        'score',
        'comments_count',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
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
