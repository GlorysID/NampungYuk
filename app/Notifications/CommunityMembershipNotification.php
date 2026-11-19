<?php

namespace App\Notifications;

use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityMembershipNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Community $community,
        public string $message,
        public ?User $actor = null,
        public ?string $url = null,
    ) {
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
        return [
            'type' => 'community_membership',
            'actor_name' => $this->actor?->name ?? $this->community->name,
            'actor_username' => $this->actor?->username,
            'actor_avatar' => $this->actor?->avatar ?? $this->community->icon,
            'community_name' => $this->community->name,
            'community_slug' => $this->community->slug,
            'message' => $this->message,
            'url' => $this->url ?? route('communities.show', $this->community->slug),
        ];
    }
}
