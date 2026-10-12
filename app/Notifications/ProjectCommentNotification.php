<?php

namespace App\Notifications;

use App\Models\ProjectComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectCommentNotification extends Notification
{
    use Queueable;

    public function __construct(public ProjectComment $comment, public bool $isReply = false)
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
        $project = $this->comment->project;
        $author = $this->comment->user;

        return [
            'type' => $this->isReply ? 'project_comment_reply' : 'project_comment',
            'actor_name' => $author?->name,
            'actor_username' => $author?->username,
            'actor_avatar' => $author?->avatar,
            'project_title' => $project?->title,
            'project_slug' => $project?->slug,
            'comment_excerpt' => mb_strimwidth($this->comment->content, 0, 80, '…'),
            'message' => $this->isReply
                ? ($author?->name ?? 'Seseorang').' membalas komentarmu'
                : ($author?->name ?? 'Seseorang').' mengomentari projectmu: '.($project?->title ?? ''),
            'url' => $project ? route('projects.show', $project->slug).'#komentar' : '#',
        ];
    }
}
