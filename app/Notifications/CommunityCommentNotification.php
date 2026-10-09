<?php

namespace App\Notifications;

use App\Models\CommunityPostComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityCommentNotification extends Notification
{
    use Queueable;

    public function __construct(public CommunityPostComment $comment)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $post = $this->comment->post;
        $author = $this->comment->user;

        return [
            'type' => 'community_comment',
            'actor_name' => $author?->name,
            'actor_username' => $author?->username,
            'actor_avatar' => $author?->avatar,
            'post_excerpt' => mb_strimwidth($post?->content ?? '', 0, 80, '…'),
            'message' => ($author?->name ?? 'Seseorang').' mengomentari postinganmu',
            'url' => $post ? route('communities.show', $post->community->slug).'#post-'.$post->id : '#',
        ];
    }
}
