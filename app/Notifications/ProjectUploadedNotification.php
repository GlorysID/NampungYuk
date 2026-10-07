<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(public Project $project)
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
            'type' => 'project_uploaded',
            'actor_name' => $this->project->user?->name,
            'actor_username' => $this->project->user?->username,
            'actor_avatar' => $this->project->user?->avatar,
            'project_title' => $this->project->title,
            'project_slug' => $this->project->slug,
            'thumbnail' => $this->project->thumbnail,
            'message' => ($this->project->user?->name ?? 'Seseorang').' mengunggah project baru: '.$this->project->title,
            'url' => route('projects.show', $this->project->slug),
        ];
    }
}
