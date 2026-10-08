<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProjectBookmark;
use App\Models\ProjectVote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the form to edit the authenticated user's profile.
     */
    public function edit(): View
    {
        $user = auth()->user();

        return view('users.edit', compact('user'));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            // Remove the previous locally-stored avatar (ignore external URLs/dicebear).
            if ($user->avatar && str_contains($user->avatar, '/storage/avatars/')) {
                $old = str_replace('/storage/', '', strstr($user->avatar, '/storage/avatars/'));
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = '/storage/'.$path;
        }

        $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'bio' => $data['bio'] ?? null,
            'github_url' => $data['github_url'] ?? null,
            ...(isset($data['avatar']) ? ['avatar' => $data['avatar']] : []),
        ]);

        return redirect()
            ->route('profile.show', $user->username)
            ->with('success', 'Profil kamu berhasil diperbarui!');
    }

    /**
     * Display the specified user's public profile.
     */
    public function show(string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();

        $currentUserId = auth()->id();
        $isOwner = $currentUserId === $user->id;

        // Only the owner sees their own private projects.
        $viewerId = $isOwner ? $currentUserId : null;

        $allProjects = $user->projects()
            ->with(['category', 'user', 'forkedFrom'])
            ->visibleTo($viewerId)
            ->latest()
            ->get();

        // Pinned projects appear first, only surfaced on the owner's view order.
        $pinnedProjects = $user->pinnedProjects()
            ->with(['category', 'user', 'forkedFrom'])
            ->visibleTo($viewerId)
            ->get();

        $pinnedIds = $pinnedProjects->pluck('id')->all();

        $projects = $user->projects()
            ->with(['category', 'user', 'forkedFrom'])
            ->visibleTo($viewerId)
            ->whereNotIn('id', $pinnedIds)
            ->latest()
            ->paginate(10);

        $comments = $user->comments()
            ->with('project')
            ->latest()
            ->take(20)
            ->get();

        $totalUpvotesReceived = $user->projects()->visibleTo($viewerId)->sum('upvotes_count');
        $totalProjects = $user->projects()->visibleTo($viewerId)->count();

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        // Likes tab is only shown to the owner.
        $likedProjects = $isOwner
            ? $user->likedProjects()->with(['category', 'user', 'forkedFrom'])->latest('project_votes.created_at')->paginate(10, ['*'], 'likes')
            : collect();

        $totalLikes = $isOwner ? $user->likedProjects()->count() : 0;

        $userBookmarkedIds = $currentUserId
            ? ProjectBookmark::where('user_id', $currentUserId)->pluck('project_id')->toArray()
            : [];
        $userVotes = $currentUserId
            ? ProjectVote::where('user_id', $currentUserId)->pluck('type', 'project_id')->toArray()
            : [];

        return view('users.show', compact(
            'user',
            'projects',
            'pinnedProjects',
            'likedProjects',
            'comments',
            'totalUpvotesReceived',
            'totalProjects',
            'totalLikes',
            'userBookmarkedIds',
            'userVotes',
            'followersCount',
            'followingCount',
            'isOwner'
        ));
    }
}
