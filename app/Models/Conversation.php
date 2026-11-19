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
        'status',
        'accepted_at',
        'initiator_id',
    ];

    /**
     * Model-level default so a freshly created instance already reports the
     * correct state before a DB round-trip (avoids '' / null ambiguity).
     */
    protected $attributes = [
        'status' => 'accepted',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * Messages in chronological order. NOTE: ordering is NOT baked into the
     * relation — call sites must add ->oldest()/->latest() explicitly so a
     * stray opposite order never silently wins a double ORDER BY.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
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
     * Find OR create the conversation between two users (order-independent).
     * WARNING: creates a row (defaulting to 'accepted') when none exists.
     * Only use this when you genuinely intend to persist — for read-only
     * lookups use findBetween() instead.
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
     * Find the conversation between two users WITHOUT creating one.
     */
    public static function findBetween(int $a, int $b): ?self
    {
        return static::where('user_one_id', min($a, $b))
            ->where('user_two_id', max($a, $b))
            ->first();
    }

    /**
     * Resolve the conversation used when $from actively sends a message to $to.
     *
     * Gate follows the Instagram/X model: a message lands directly (status
     * 'accepted') when the RECIPIENT already follows the SENDER. Otherwise the
     * message becomes a pending "message request" the recipient must accept.
     *
     * Status is re-evaluated on every send, but acceptance is MONOTONIC:
     *  - a brand-new conversation gets the gate status computed from follows;
     *  - an existing 'pending' conversation is upgraded to 'accepted' if the
     *    rule is now satisfied;
     *  - an existing 'accepted' conversation is NEVER downgraded — once a thread
     *    is open it stays open, even after the follow direction reverses or the
     *    messages start flowing the other way.
     */
    public static function forMessage(int $from, int $to): self
    {
        $conversation = static::findBetween($from, $to) ?? new static([
            'user_one_id' => min($from, $to),
            'user_two_id' => max($from, $to),
        ]);

        // New conversation → the sender is the initiator and the gate applies.
        if (! $conversation->exists) {
            $conversation->initiator_id = $from;
            $conversation->status = static::gateStatusFor($from, $to);
            $conversation->save();

            return $conversation;
        }

        // Existing conversation: allow pending → accepted upgrades only when the
        // original initiator's request now satisfies the recipient's gate (never
        // let a recipient bypass explicit accept by replying, and never downgrade).
        if ($conversation->isPending()
            && ! $conversation->accepted_at
            && (int) $conversation->initiator_id === $from
            && static::gateStatusFor($from, $to) === 'accepted') {
            $conversation->status = 'accepted';
            $conversation->save();
        }

        return $conversation;
    }

    /**
     * Whether a message from $from to $to may land directly ('accepted') or must
     * become a pending message request ('pending').
     *
     * Default policy ('everyone') lets anyone chat directly without permission.
     * Under 'following_only', accounts the recipient follows land directly while
     * everyone else enters as a pending message request.
     */
    public static function gateStatusFor(int $from, int $to): string
    {
        $sender = User::find($from);
        $recipient = User::find($to);

        if (! $sender || ! $recipient) {
            return 'pending';
        }

        return $recipient->requiresMessageRequestFrom($sender) ? 'pending' : 'accepted';
    }

    /**
     * The other participant for a given user id.
     */
    public function otherUser(int $userId): ?User
    {
        return $userId === $this->user_one_id ? $this->userTwo : $this->userOne;
    }

    /**
     * Whether the given user id is a participant of this conversation.
     */
    public function isParticipant(?int $userId): bool
    {
        return $userId !== null
            && ((int) $this->user_one_id === (int) $userId || (int) $this->user_two_id === (int) $userId);
    }

    /**
     * Unread incoming message count for a given user.
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Whether this conversation is an unaccepted message request.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Whether the recipient has accepted the conversation (or it was never gated).
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
