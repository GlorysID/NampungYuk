<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message)
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
        $sender = $this->message->sender;

        return [
            'type' => 'new_message',
            'conversation_id' => $this->message->conversation_id,
            'actor_id' => $sender?->id,
            'actor_name' => $sender?->name,
            'actor_username' => $sender?->username,
            'actor_avatar' => $sender?->avatar,
            'message' => ($sender?->name ?? 'Seseorang').' mengirim pesan: '.mb_strimwidth(trim($this->message->body), 0, 60, '…'),
            'url' => $sender ? route('chat.show', $sender->username) : route('chat.index'),
        ];
    }
}
