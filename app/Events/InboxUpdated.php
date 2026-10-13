<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies the recipient's personal channel that their inbox changed
 * (new message, updated preview, unread badge) so the inbox can update live.
 */
class InboxUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $recipientId,
        public Message $message,
    ) {
        //
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->recipientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'inbox.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender?->name,
            'sender_username' => $this->message->sender?->username,
            'body' => $this->message->body,
            'time' => $this->message->created_at?->format('H:i'),
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }
}
