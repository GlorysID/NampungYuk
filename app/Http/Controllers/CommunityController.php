<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Browse / explore communities.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $tab = $request->query('tab', 'untukmu');
        $userId = Auth::id();

        // Communities the viewer may see.
        $visibleCommunityIds = Community::visibleTo($userId)->pluck('id')->toArray();

        $joinedIds = $userId
            ? CommunityMember::where('user_id', $userId)->pluck('community_id')->toArray()
            : [];

        // Feed of posts across visible communities (X-style timeline).
        $postsQuery = CommunityPost::with(['user', 'community'])
            ->whereIn('community_id', $visibleCommunityIds);

        switch ($tab) {
            case 'diikuti':
                $postsQuery->whereIn('community_id', $joinedIds);
                break;
            case 'populer':
                $postsQuery->orderByDesc('is_pinned')->orderByDesc('score');
                break;
            case 'baru':
                $postsQuery->orderByDesc('is_pinned')->orderByDesc('created_at');
                break;
            case 'untukmu':
            default:
                $postsQuery->orderByDesc('is_pinned')->orderByDesc('created_at');
                break;
        }

        if ($search) {
            $postsQuery->where('content', 'like', "%{$search}%");
        }

        $posts = $postsQuery->paginate(12)->withQueryString();

        $userVotes = $userId
            ? \App\Models\CommunityPostVote::where('user_id', $userId)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('type', 'community_post_id')->toArray()
            : [];

        // Right sidebar: top communities + followed communities.
        $topCommunities = Community::visibleTo($userId)
            ->orderByDesc('members_count')
            ->take(5)
            ->get();

        $followedCommunities = $userId
            ? Community::whereIn('id', $joinedIds)->orderByDesc('members_count')->take(5)->get()
            : collect();

        return view('communities.index', compact('posts', 'userVotes', 'joinedIds', 'search', 'tab', 'topCommunities', 'followedCommunities', 'userId'));
    }

    /**
     * Show a single community with its post feed.
     */
    public function show(string $slug, Request $request): View
    {
        $userId = Auth::id();

        $community = Community::with('owner')
            ->where('slug', $slug)
            ->firstOrFail();

        // Private community: only members/owner may view.
        if ($community->isPrivate() && ! $community->hasMember($userId) && $community->owner_id !== $userId) {
            abort(404);
        }

        $isMember = $community->hasMember($userId);
        $isOwner = $community->owner_id === $userId;
        $isModerator = $community->isModerator($userId);

        $tab = $request->query('tab', 'terbaru');
        $posts = $community->posts()
            ->with(['user', 'comments.user'])
            ->when($tab === 'terjawab', fn ($q) => $q->where('is_answered', true))
            ->orderByDesc('is_pinned')
            ->sortTab($tab)
            ->paginate(10)
            ->withQueryString();

        $members = $community->memberUsers()->orderByDesc('community_members.created_at')->take(12)->get();

        // Which posts the user has voted on.
        $userVotes = $userId
            ? \App\Models\CommunityPostVote::where('user_id', $userId)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('type', 'community_post_id')->toArray()
            : [];

        return view('communities.show', compact('community', 'posts', 'members', 'isMember', 'isOwner', 'isModerator', 'userVotes', 'tab'));
    }

    /**
     * Create a new community.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:280'],
            'visibility' => ['nullable', 'in:public,private'],
            'icon' => ['nullable', 'url', 'max:255'],
        ]);

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Community::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }

        $community = Community::create([
            'owner_id' => $user->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
            'icon' => $data['icon'] ?? null,
            'members_count' => 1,
        ]);

        // Owner auto-joins as owner.
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        return redirect()->route('communities.show', $community->slug)
            ->with('success', 'Komunitas berhasil dibuat!');
    }

    /**
     * Toggle join/leave a community.
     */
    public function toggleJoin(Request $request, Community $community): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            // Owners cannot leave their own community.
            if ($community->owner_id === $user->id) {
                $msg = 'Pemilik tidak bisa keluar dari komunitas sendiri.';
                if ($request->wantsJson()) {
                    return response()->json(['message' => $msg], 422);
                }

                return back()->with('error', $msg);
            }

            $existing->delete();
            $community->decrement('members_count');
            $joined = false;
            $message = 'Kamu keluar dari '.$community->name;
        } else {
            CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => $user->id,
                'role' => 'member',
            ]);
            $community->increment('members_count');
            $joined = true;
            $message = 'Kamu bergabung dengan '.$community->name;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'joined' => $joined,
                'members_count' => $community->fresh()->members_count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
