<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(public User $follower)
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
        return [
            'type' => 'new_follower',
            'actor_name' => $this->follower->name,
            'actor_username' => $this->follower->username,
            'actor_avatar' => $this->follower->avatar,
            'message' => $this->follower->name.' mulai mengikuti kamu',
            'url' => route('profile.show', $this->follower->username),
        ];
    }
}
