@foreach($posts as $post)
    <x-community-post :post="$post" :initial-vote="$userVotes[$post->id] ?? null" :can-comment="($allowComments ?? $isMember) && ! $post->is_locked" :can-moderate="$isModerator" :author-role="$roleMap[$post->user_id] ?? null" />
@endforeach
