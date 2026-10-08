<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProjectBookmark;
use App\Models\ProjectRepost;
use App\Models\ProjectVote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
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

        if ($request->hasFile('banner')) {
            if ($user->banner && str_contains($user->banner, '/storage/banners/')) {
                $oldBanner = str_replace('/storage/', '', strstr($user->banner, '/storage/banners/'));
                Storage::disk('public')->delete($oldBanner);
            }

            $bannerPath = $request->file('banner')->store('banners', 'public');
            $data['banner'] = '/storage/'.$bannerPath;
        }

        $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'bio' => $data['bio'] ?? null,
            ...(isset($data['avatar']) ? ['avatar' => $data['avatar']] : []),
            ...(isset($data['banner']) ? ['banner' => $data['banner']] : []),
        ]);

        // Sync dynamic profile links (max 6, ordered).
        if ($request->has('links')) {
            $links = collect($request->input('links', []))
                ->filter(fn ($l) => ! empty(trim((string) ($l['url'] ?? ''))))
                ->take(6)
                ->values();

            $user->links()->delete();
            foreach ($links as $i => $link) {
                $user->links()->create([
                    'label' => trim((string) ($link['label'] ?? '')) ?: 'Link',
                    'url' => trim((string) $link['url']),
                    'sort_order' => $i,
                ]);
            }
        }

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
            ->with(['category', 'user', 'repostedFrom'])
            ->visibleTo($viewerId)
            ->latest()
            ->get();

        // Pinned projects appear first, only surfaced on the owner's view order.
        $pinnedProjects = $user->pinnedProjects()
            ->with(['category', 'user', 'repostedFrom'])
            ->visibleTo($viewerId)
            ->get();

        $pinnedIds = $pinnedProjects->pluck('id')->all();

        $projects = $user->projects()
            ->with(['category', 'user', 'repostedFrom'])
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
            ? $user->likedProjects()->with(['category', 'user', 'repostedFrom'])->latest('project_votes.created_at')->paginate(10, ['*'], 'likes')
            : collect();

        $totalLikes = $isOwner ? $user->likedProjects()->count() : 0;

        // Reposted projects — what this user reshared (visible to everyone).
        $repostedProjects = $user->repostedProjects()
            ->with(['category', 'user', 'repostedFrom'])
            ->visibleTo($viewerId)
            ->latest('project_reposts.created_at')
            ->paginate(10, ['*'], 'reposts');

        $totalReposts = $user->reposts()->count();

        $userBookmarkedIds = $currentUserId
            ? ProjectBookmark::where('user_id', $currentUserId)->pluck('project_id')->toArray()
            : [];
        $userVotes = $currentUserId
            ? ProjectVote::where('user_id', $currentUserId)->pluck('type', 'project_id')->toArray()
            : [];

        $userRepostedIds = $currentUserId
            ? ProjectRepost::where('user_id', $currentUserId)->pluck('project_id')->toArray()
            : [];

        return view('users.show', compact(
            'user',
            'projects',
            'pinnedProjects',
            'likedProjects',
            'repostedProjects',
            'comments',
            'totalUpvotesReceived',
            'totalProjects',
            'totalLikes',
            'totalReposts',
            'userBookmarkedIds',
            'userVotes',
            'userRepostedIds',
            'followersCount',
            'followingCount',
            'isOwner'
        ));
    }
}
