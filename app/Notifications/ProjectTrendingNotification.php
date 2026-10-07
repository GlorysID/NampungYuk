<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectTrendingNotification extends Notification
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
            'type' => 'project_trending',
            'project_title' => $this->project->title,
            'project_slug' => $this->project->slug,
            'thumbnail' => $this->project->thumbnail,
            'score' => $this->project->score,
            'message' => 'Project kamu "'.$this->project->title.'" sedang populer! ('.$this->project->score.' pts)',
            'url' => route('projects.show', $this->project->slug),
        ];
    }
}
