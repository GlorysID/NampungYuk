<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectForkedNotification extends Notification
{
    use Queueable;

    public function __construct(public Project $fork, public User $by)
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
            'type' => 'project_forked',
            'actor_name' => $this->by->name,
            'actor_username' => $this->by->username,
            'actor_avatar' => $this->by->avatar,
            'project_title' => $this->fork->title,
            'project_slug' => $this->fork->slug,
            'thumbnail' => $this->fork->thumbnail,
            'message' => $this->by->name.' mem-fork project kamu: '.$this->fork->title,
            'url' => route('projects.show', $this->fork->slug),
        ];
    }
}
