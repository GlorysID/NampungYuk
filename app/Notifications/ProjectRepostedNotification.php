<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectRepostedNotification extends Notification
{
    use Queueable;

    public function __construct(public Project $project, public User $by)
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
            'type' => 'project_reposted',
            'actor_name' => $this->by->name,
            'actor_username' => $this->by->username,
            'actor_avatar' => $this->by->avatar,
            'project_title' => $this->project->title,
            'project_slug' => $this->project->slug,
            'thumbnail' => $this->project->thumbnail,
            'message' => $this->by->name.' membagikan ulang project kamu: '.$this->project->title,
            'url' => route('projects.show', $this->project->slug),
        ];
    }
}
