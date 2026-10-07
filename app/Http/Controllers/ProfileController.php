<?php

namespace App\Http\Controllers;

use App\Models\ProjectBookmark;
use App\Models\ProjectVote;
use App\Models\User;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the specified user's public profile.
     */
    public function show(string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();

        $projects = $user->projects()
            ->with(['category', 'user'])
            ->latest()
            ->paginate(10);

        $comments = $user->comments()
            ->with('project')
            ->latest()
            ->take(20)
            ->get();

        $totalUpvotesReceived = $user->projects()->sum('upvotes_count');
        $totalProjects = $user->projects()->count();

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        $currentUserId = auth()->id();
        $userBookmarkedIds = $currentUserId
            ? ProjectBookmark::where('user_id', $currentUserId)->pluck('project_id')->toArray()
            : [];
        $userVotes = $currentUserId
            ? ProjectVote::where('user_id', $currentUserId)->pluck('type', 'project_id')->toArray()
            : [];

        return view('users.show', compact('user', 'projects', 'comments', 'totalUpvotesReceived', 'totalProjects', 'userBookmarkedIds', 'userVotes', 'followersCount', 'followingCount'));
    }
}
