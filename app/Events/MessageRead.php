<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int  $conversationId
     * @param  int  $readerId  The user who read the messages.
     * @param  int|null  $upToMessageId  Highest message id marked as read.
     */
    public function __construct(
        public int $conversationId,
        public int $readerId,
        public ?int $upToMessageId = null,
    ) {
        //
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.'.$this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'reader_id' => $this->readerId,
            'up_to_message_id' => $this->upToMessageId,
            'read_at' => now()->toIso8601String(),
        ];
    }
}
