<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Only participants may listen to a conversation channel.
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    return in_array((int) $user->id, [(int) $conversation->user_one_id, (int) $conversation->user_two_id], true);
});

// Only participants of a space may listen to its signaling channel.
Broadcast::channel('space.{spaceId}', function ($user, $spaceId) {
    return \App\Models\SpaceParticipant::where('space_id', $spaceId)
        ->where('user_id', $user->id)
        ->exists();
});
