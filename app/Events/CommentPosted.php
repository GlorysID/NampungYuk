<?php

namespace App\Events;

use App\Models\ProjectComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ProjectComment $comment)
    {
        //
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('feed'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.posted';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'project_id' => $this->comment->project_id,
            'author' => $this->comment->user?->name ?? $this->comment->guest_name ?? 'Anonim',
            'content' => $this->comment->content,
            'created_at' => $this->comment->created_at?->diffForHumans(),
        ];
    }
}
