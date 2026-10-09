@foreach($projects as $project)
    @php
        $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
        $initialVote = $userVotes[$project->id] ?? null;
    @endphp
    <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="in_array($project->id, $userRepostedIds ?? [])" />
@endforeach
