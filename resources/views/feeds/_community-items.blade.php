@foreach($posts as $post)
    <x-community-post :post="$post" :initial-vote="$userVotes[$post->id] ?? null" :can-comment="false" :can-moderate="false" :show-community="true" />
@endforeach
