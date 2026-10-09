<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest();
    }

    public function lastMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /**
     * Find or create the conversation between two users (order-independent).
     */
    public static function between(int $a, int $b): self
    {
        $low = min($a, $b);
        $high = max($a, $b);

        return static::firstOrCreate([
            'user_one_id' => $low,
            'user_two_id' => $high,
        ]);
    }

    /**
     * The other participant for a given user id.
     */
    public function otherUser(int $userId): ?User
    {
        return $userId === $this->user_one_id ? $this->userTwo : $this->userOne;
    }
}
