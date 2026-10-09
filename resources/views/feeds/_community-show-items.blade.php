@foreach($posts as $post)
    <x-community-post :post="$post" :initial-vote="$userVotes[$post->id] ?? null" :can-comment="$isMember" :can-moderate="$isModerator" />
@endforeach
