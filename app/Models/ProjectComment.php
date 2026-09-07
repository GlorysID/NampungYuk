<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'guest_name',
        'parent_id',
        'content',
        'upvotes_count',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function authorName(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Anon Programmer';
    }

    public function authorAvatar(): string
    {
        return $this->user?->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($this->authorName());
    }
}
