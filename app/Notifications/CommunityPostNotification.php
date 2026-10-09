<?php

namespace App\Notifications;

use App\Models\CommunityPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityPostNotification extends Notification
{
    use Queueable;

    public function __construct(public CommunityPost $post)
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
        $community = $this->post->community;
        $author = $this->post->user;

        return [
            'type' => 'community_post',
            'actor_name' => $author?->name,
            'actor_username' => $author?->username,
            'actor_avatar' => $author?->avatar,
            'community_name' => $community?->name,
            'community_slug' => $community?->slug,
            'post_excerpt' => mb_strimwidth($this->post->content, 0, 80, '…'),
            'message' => ($author?->name ?? 'Seseorang').' memposting di '.($community?->name ?? 'komunitas'),
            'url' => $community ? route('communities.show', $community->slug) : '#',
        ];
    }
}
