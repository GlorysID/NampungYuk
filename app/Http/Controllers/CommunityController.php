<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\CommunityPostVote;
use App\Models\Space;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Browse / explore communities (Hybrid 3B: discussions feed + directory).
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $tab = $request->query('tab', 'untukmu');
        $view = $request->query('view', 'feed'); // 'feed' (Linimasa Diskusi) or 'direktori' (Katalog Komunitas)
        $userId = Auth::id();

        $joinedIds = $userId
            ? CommunityMember::where('user_id', $userId)->pluck('community_id')->toArray()
            : [];

        $visibleIds = Community::visibleTo($userId)->pluck('id')->toArray();

        // 1. Discussions Feed Query (CommunityPost)
        $postQuery = CommunityPost::with(['user', 'community', 'comments.user'])
            ->whereIn('community_id', $visibleIds);

        if ($search) {
            $postQuery->where('content', 'like', "%{$search}%");
        }

        switch ($tab) {
            case 'diikuti':
                $postQuery->whereIn('community_id', $joinedIds)->latest();
                break;
            case 'populer':
                $postQuery->orderByDesc('is_pinned')->orderByDesc('score')->latest();
                break;
            case 'baru':
                $postQuery->orderByDesc('is_pinned')->latest();
                break;
            case 'untukmu':
            default:
                $postQuery->orderByDesc('is_pinned')->latest();
                break;
        }

        $posts = $postQuery->paginate(12)->withQueryString();

        // 2. Directory Query (Community Cards)
        $communityQuery = Community::with('owner')
            ->visibleTo($userId)
            ->withCount('members');

        if ($search) {
            $communityQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('topic', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        switch ($tab) {
            case 'diikuti':
                $communityQuery->whereIn('id', $joinedIds)->orderByDesc('members_count');
                break;
            case 'populer':
                $communityQuery->orderByDesc('members_count');
                break;
            case 'baru':
                $communityQuery->latest();
                break;
            case 'untukmu':
            default:
                $communityQuery->orderByDesc('is_featured')->orderByDesc('members_count');
                break;
        }

        $communities = $communityQuery->paginate(12)->withQueryString();

        // 3. Right sidebar: top + followed communities
        $topCommunities = Community::visibleTo($userId)
            ->orderByDesc('members_count')
            ->take(5)
            ->get();

        $followedCommunities = $userId
            ? Community::whereIn('id', $joinedIds)->orderByDesc('members_count')->take(5)->get()
            : collect();

        // 4. User votes on community posts
        $userVotes = $userId
            ? CommunityPostVote::where('user_id', $userId)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('type', 'community_post_id')
                ->toArray()
            : [];

        // 5. Active Live Stage / Space
        $liveSpace = Space::with('host')->where('status', 'live')->first();

        return view('communities.index', compact(
            'posts',
            'communities',
            'joinedIds',
            'search',
            'tab',
            'view',
            'topCommunities',
            'followedCommunities',
            'userId',
            'userVotes',
            'liveSpace'
        ));
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
        $search = $request->query('q');
        $posts = $community->posts()
            ->with(['user', 'comments.user'])
            ->when($tab === 'terjawab', fn ($q) => $q->where('is_answered', true))
            ->when($search, fn ($q) => $q->where('content', 'like', "%{$search}%"))
            ->orderByDesc('is_pinned')
            ->sortTab($tab)
            ->paginate(10)
            ->withQueryString();

        $members = $community->memberUsers()->orderByDesc('community_members.created_at')->take(12)->get();

        // Owner: full member list with roles for moderation.
        $memberList = $isOwner
            ? $community->memberUsers()->orderByDesc('community_members.created_at')->get()
            : collect();

        // Which posts the user has voted on.
        $userVotes = $userId
            ? CommunityPostVote::where('user_id', $userId)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('type', 'community_post_id')->toArray()
            : [];

        return view('communities.show', compact('community', 'posts', 'members', 'memberList', 'isMember', 'isOwner', 'isModerator', 'userVotes', 'tab', 'search'));
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
            'topic' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'rules' => ['nullable', 'string', 'max:1000'],
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
            'topic' => $data['topic'] ?? null,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'rules' => $data['rules'] ?? null,
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

    /**
     * Promote a member to moderator / demote a moderator (owner only).
     */
    public function toggleModerator(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || $community->owner_id !== $auth->id) {
            abort(403);
        }

        if ($user->id === $community->owner_id) {
            return back()->with('error', 'Pemilik tidak bisa diubah rolenya.');
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $member->update([
            'role' => $member->role === 'mod' ? 'member' : 'mod',
        ]);

        return back()->with('success', $member->role === 'mod'
            ? $user->name.' diangkat menjadi moderator.'
            : $user->name.' dicopot dari moderator.');
    }
}
