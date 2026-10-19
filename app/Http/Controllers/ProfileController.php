<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProjectBookmark;
use App\Models\ProjectRepost;
use App\Models\ProjectVote;
use App\Models\User;
use App\Services\MediaOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private MediaOptimizer $media) {}

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

            // Avatars are shown small (max ~256px) — shrink aggressively.
            $path = $this->media->storeImage($request->file('avatar'), 'avatars', 'avatar');
            $data['avatar'] = '/storage/'.$path;
        }

        if ($request->hasFile('banner')) {
            if ($user->banner && str_contains($user->banner, '/storage/banners/')) {
                $oldBanner = str_replace('/storage/', '', strstr($user->banner, '/storage/banners/'));
                Storage::disk('public')->delete($oldBanner);
            }

            $bannerPath = $this->media->storeImage($request->file('banner'), 'banners', 'thumb');
            $data['banner'] = '/storage/'.$bannerPath;
        }

        $usernameChanged = $user->username !== $data['username'];

        $updateData = [
            'name' => $data['name'],
            'username' => $data['username'],
            'bio' => $data['bio'] ?? null,
            ...(isset($data['avatar']) ? ['avatar' => $data['avatar']] : []),
            ...(isset($data['banner']) ? ['banner' => $data['banner']] : []),
        ];

        if ($usernameChanged) {
            $updateData['username_changed_at'] = now();
        }

        $user->update($updateData);

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
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password akun Anda berhasil diperbarui!');
    }

    /**
     * Display the specified user's public profile.
     */
    public function show(string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();

        $currentUserId = auth()->id();
        $isOwner = $currentUserId === $user->id;
        $isAdmin = auth()->user()?->is_admin ?? false;

        // Only the owner or admin sees private and warned projects.
        $viewerId = ($isOwner || $isAdmin) ? $currentUserId : null;

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

        // Only show comments the viewer may see — i.e. comments on projects
        // that are still visible (a warned/private project must not leak its
        // title through the owner's activity feed).
        $comments = $user->comments()
            ->with('project')
            ->whereHas('project', fn ($q) => $q->visibleTo($viewerId))
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
