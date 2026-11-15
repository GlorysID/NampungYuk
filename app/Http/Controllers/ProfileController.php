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
            ...(array_key_exists('github_url', $data) ? ['github_url' => $data['github_url']] : []),
            ...(array_key_exists('website_url', $data) ? ['website_url' => $data['website_url']] : []),
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
     * Display the authenticated user's account settings page.
     */
    public function settings(Request $request): View
    {
        return view('settings.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasExistingPassword = filled($user->password);

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($hasExistingPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules, [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $message = $hasExistingPassword
            ? 'Password akun Anda berhasil diperbarui!'
            : 'Password akun berhasil dibuat!';

        return back()
            ->with('success', $message)
            ->with('settings_tab', 'keamanan');
    }

    /**
     * Display the specified user's public profile.
     */
    public function show(Request $request, string $username): View
    {
        $user = User::where('username', $username)->firstOrFail();

        $currentUserId = auth()->id();
        $isOwner = $currentUserId === $user->id;
        $isAdmin = auth()->user()?->is_admin ?? false;
        $search = trim((string) $request->query('q', ''));

        // Only the owner or admin sees private and warned projects.
        $viewerId = ($isOwner || $isAdmin) ? $currentUserId : null;

        // Pinned projects appear first, only surfaced when not actively filtering.
        $pinnedProjects = $search === ''
            ? $user->pinnedProjects()
                ->with(['category', 'user', 'repostedFrom'])
                ->visibleTo($viewerId)
                ->get()
            : collect();

        $pinnedIds = $pinnedProjects->pluck('id')->all();

        $projectsQuery = $user->projects()
            ->with(['category', 'user', 'repostedFrom'])
            ->visibleTo($viewerId)
            ->search($search);

        if ($search === '' && ! empty($pinnedIds)) {
            $projectsQuery->whereNotIn('id', $pinnedIds);
        }

        $projects = $projectsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Only show comments the viewer may see — i.e. comments on projects
        // that are still visible (a warned/private project must not leak its
        // title through the owner's activity feed).
        $commentsQuery = $user->comments()
            ->with('project')
            ->whereHas('project', fn ($q) => $q->visibleTo($viewerId));

        if ($search !== '') {
            $commentsQuery->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('project', fn ($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        $comments = $commentsQuery
            ->latest()
            ->take(20)
            ->get();

        $totalUpvotesReceived = $user->projects()->visibleTo($viewerId)->sum('upvotes_count');
        $totalProjects = $user->projects()->visibleTo($viewerId)->count();

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        // Likes tab is only shown to the owner.
        $likedProjects = $isOwner
            ? $user->likedProjects()
                ->with(['category', 'user', 'repostedFrom'])
                ->visibleTo($viewerId)
                ->search($search)
                ->latest('project_votes.created_at')
                ->paginate(10, ['*'], 'likes')
                ->withQueryString()
            : collect();

        $totalLikes = $isOwner ? $user->likedProjects()->count() : 0;

        // Reposted projects — what this user reshared (visible to everyone).
        $repostedProjects = $user->repostedProjects()
            ->with(['category', 'user', 'repostedFrom'])
            ->visibleTo($viewerId)
            ->search($search)
            ->latest('project_reposts.created_at')
            ->paginate(10, ['*'], 'reposts')
            ->withQueryString();

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
            'isOwner',
            'search'
        ));
    }

    /**
     * List the accounts following this user.
     */
    public function followers(string $username): View
    {
        return $this->followList($username, 'followers');
    }

    /**
     * List the accounts this user follows.
     */
    public function following(string $username): View
    {
        return $this->followList($username, 'following');
    }

    /**
     * Shared renderer for the follower / following directory.
     */
    private function followList(string $username, string $mode): View
    {
        $user = User::where('username', $username)->firstOrFail();

        $people = $user->{$mode}()
            ->orderByDesc('follows.created_at')
            ->paginate(24);

        return view('users.follows', compact('user', 'people', 'mode'));
    }
}
