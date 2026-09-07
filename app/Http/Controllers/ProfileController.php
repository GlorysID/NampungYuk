<?php

namespace App\Http\Controllers;

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

        return view('users.show', compact('user', 'projects', 'comments', 'totalUpvotesReceived', 'totalProjects'));
    }
}
