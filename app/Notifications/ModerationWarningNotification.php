<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModerationWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $notes,
        public string $url,
        public ?User $actor = null,
        public string $scope = 'account',
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'moderation_warning',
            'scope' => $this->scope,
            'actor_name' => 'Tim Moderasi NampungYuk',
            'actor_username' => $this->actor?->username,
            'actor_avatar' => null,
            'message' => $this->message,
            'warning_excerpt' => $this->notes,
            'url' => $this->url,
        ];
    }
}
