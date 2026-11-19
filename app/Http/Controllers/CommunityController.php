<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\CommunityPostVote;
use App\Models\Space;
use App\Models\User;
use App\Rules\SafeUrl;
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

        // 1. Discussions Feed Query (approved posts only)
        $postQuery = CommunityPost::with(['user', 'community', 'comments.user'])
            ->approved()
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

        // 4. Communities managed by the current user (owner/admin/mod)
        $managedCommunities = collect();
        $managedRoles = [];
        if ($userId) {
            $managedRoles = CommunityMember::where('user_id', $userId)
                ->whereIn('role', ['owner', 'admin', 'mod'])
                ->pluck('role', 'community_id')
                ->all();

            $ownedIds = Community::where('owner_id', $userId)->pluck('id')->all();
            foreach ($ownedIds as $oid) {
                $managedRoles[$oid] = 'owner';
            }

            if (! empty($managedRoles)) {
                $managedCommunities = Community::whereIn('id', array_keys($managedRoles))
                    ->withCount(['pendingJoinRequests', 'pendingPosts'])
                    ->orderBy('name')
                    ->get();
            }
        }

        // 5. User votes on community posts
        $userVotes = $userId
            ? CommunityPostVote::where('user_id', $userId)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('type', 'community_post_id')
                ->toArray()
            : [];

        // 6. Active Live Stage / Space
        $liveSpace = Space::with('host')->where('status', 'live')->first();

        /** @var User|null $authUser */
        $authUser = Auth::user();
        $followersCount = $authUser ? $authUser->followers()->count() : 0;
        $minFollowersToCreate = Community::MIN_FOLLOWERS_TO_CREATE;
        $canCreateCommunity = Community::canBeCreatedBy($authUser);

        return view('communities.index', compact(
            'posts',
            'communities',
            'joinedIds',
            'search',
            'tab',
            'view',
            'topCommunities',
            'followedCommunities',
            'managedCommunities',
            'managedRoles',
            'userId',
            'userVotes',
            'liveSpace',
            'followersCount',
            'minFollowersToCreate',
            'canCreateCommunity'
        ));
    }

    /**
     * Show a single community with its post feed, members, and rules.
     */
    public function show(string $slug, Request $request): View
    {
        $userId = Auth::id();
        /** @var User|null $authUser */
        $authUser = Auth::user();

        $community = Community::with('owner')
            ->where('slug', $slug)
            ->firstOrFail();

        $isMember = $community->hasMember($userId);
        $isOwner = $community->isOwner($userId);
        $isAdmin = $community->isAdmin($userId);
        $isModerator = $community->isModerator($userId);
        $myRole = $community->roleOf($userId);
        $memberRecord = $community->memberRecord($userId);
        $isMuted = $memberRecord?->isMuted() ?? false;
        $isBanned = $community->isBanned($userId);

        $isLockedPrivate = $community->isPrivate()
            && ! $isMember
            && ! $isOwner
            && ! ($authUser?->isAdmin() ?? false);

        $pendingJoinRequest = $userId
            ? $community->joinRequests()->where('user_id', $userId)->where('status', 'pending')->first()
            : null;

        $tab = $request->query('tab', 'terbaru');
        $section = $request->query('section', 'diskusi');
        if (! in_array($section, ['diskusi', 'anggota', 'tentang'], true)) {
            $section = 'diskusi';
        }
        $search = $request->query('q');

        if ($isBanned || $isLockedPrivate) {
            $posts = collect();
            $members = collect();
            $memberList = collect();
            $allMembers = collect();
            $adminModerators = collect();
            $myPendingPosts = collect();
            $userVotes = [];
            $roleMap = [];
            $pendingModerationCount = 0;

            return view('communities.show', compact(
                'community',
                'posts',
                'members',
                'memberList',
                'allMembers',
                'adminModerators',
                'myPendingPosts',
                'isMember',
                'isOwner',
                'isAdmin',
                'isModerator',
                'myRole',
                'memberRecord',
                'isMuted',
                'isBanned',
                'isLockedPrivate',
                'pendingJoinRequest',
                'pendingModerationCount',
                'roleMap',
                'userVotes',
                'tab',
                'section',
                'search'
            ));
        }

        $posts = $community->approvedPosts()
            ->with(['user', 'comments.user'])
            ->when($tab === 'terjawab', fn ($q) => $q->where('is_answered', true))
            ->when($search, fn ($q) => $q->where('content', 'like', "%{$search}%"))
            ->orderByDesc('is_pinned')
            ->sortTab($tab)
            ->paginate(10)
            ->withQueryString();

        $members = $community->memberUsers()->orderByDesc('community_members.created_at')->take(12)->get();

        $memberList = $isOwner
            ? $community->memberUsers()->orderByDesc('community_members.created_at')->get()
            : collect();

        $adminModerators = $community->memberUsers()
            ->whereIn('community_members.role', ['owner', 'admin', 'mod'])
            ->orderByRaw("CASE community_members.role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")
            ->orderBy('users.name')
            ->get();

        $allMembers = $section === 'anggota'
            ? $community->memberUsers()
                ->orderByRaw("CASE community_members.role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 WHEN 'mod' THEN 2 ELSE 3 END")
                ->orderBy('users.name')
                ->paginate(24)
                ->withQueryString()
            : collect();

        $roleMap = $community->members()
            ->whereIn('role', ['owner', 'admin', 'mod'])
            ->pluck('role', 'user_id')
            ->all();
        $roleMap[$community->owner_id] = 'owner';

        $myPendingPosts = ($isMember && $userId)
            ? $community->pendingPosts()->where('user_id', $userId)->latest()->get()
            : collect();

        $pendingModerationCount = $isModerator
            ? ($community->pendingJoinRequests()->count() + $community->pendingPosts()->count())
            : 0;

        $userVotes = $userId
            ? CommunityPostVote::where('user_id', $userId)
                ->whereIn('community_post_id', $posts->pluck('id'))
                ->pluck('type', 'community_post_id')->toArray()
            : [];

        return view('communities.show', compact(
            'community',
            'posts',
            'members',
            'memberList',
            'allMembers',
            'adminModerators',
            'myPendingPosts',
            'isMember',
            'isOwner',
            'isAdmin',
            'isModerator',
            'myRole',
            'memberRecord',
            'isMuted',
            'isBanned',
            'isLockedPrivate',
            'pendingJoinRequest',
            'pendingModerationCount',
            'roleMap',
            'userVotes',
            'tab',
            'section',
            'search'
        ));
    }

    /**
     * Create a new community.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! Community::canBeCreatedBy($user)) {
            $followersCount = $user->followers()->count();
            $msg = 'Minimal '.Community::MIN_FOLLOWERS_TO_CREATE." pengikut untuk membuat komunitas baru (saat ini {$followersCount} pengikut).";

            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 403);
            }

            abort(403, $msg);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'topic' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'rules' => ['nullable', 'string', 'max:1000'],
            'welcome_message' => ['nullable', 'string', 'max:500'],
            'visibility' => ['nullable', 'in:public,private'],
            'join_policy' => ['nullable', 'in:open,approval'],
            'post_policy' => ['nullable', 'in:all_members,approval_required,admins_only'],
            'icon' => ['nullable', 'string', 'max:255', new SafeUrl],
        ]);

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Community::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }

        $visibility = $data['visibility'] ?? 'public';
        $joinPolicy = $visibility === 'private' ? 'approval' : ($data['join_policy'] ?? 'open');

        $community = Community::create([
            'owner_id' => $user->id,
            'name' => $data['name'],
            'topic' => $data['topic'] ?? null,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'rules' => $data['rules'] ?? null,
            'welcome_message' => $data['welcome_message'] ?? null,
            'visibility' => $visibility,
            'join_policy' => $joinPolicy,
            'post_policy' => $data['post_policy'] ?? 'all_members',
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
     * Toggle join/leave or submit a join approval request.
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

        if ($community->isBanned($user->id)) {
            $msg = 'Kamu diblokir dari komunitas ini.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 403);
            }

            abort(403, $msg);
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
            if ($community->members_count > 0) {
                $community->decrement('members_count');
            }
            $joined = false;
            $message = 'Kamu keluar dari '.$community->name;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'joined' => false,
                    'pending' => false,
                    'members_count' => $community->fresh()->members_count,
                    'message' => $message,
                ]);
            }

            return back()->with('success', $message);
        }

        // Communities requiring approval create/update a pending join request.
        if ($community->requiresJoinApproval()) {
            $questions = array_values(array_filter(
                (array) ($community->join_questions ?? []),
                fn ($q) => is_string($q) && trim($q) !== ''
            ));

            $rules = ['note' => ['nullable', 'string', 'max:500']];
            if (! empty($questions) && ! $request->wantsJson()) {
                $rules['answers'] = ['required', 'array', 'size:'.count($questions)];
                $rules['answers.*'] = ['required', 'string', 'max:500'];
            } else {
                $rules['answers'] = ['nullable', 'array', 'max:3'];
                $rules['answers.*'] = ['nullable', 'string', 'max:500'];
            }

            $validated = $request->validate($rules);

            $pairedAnswers = [];
            foreach ($questions as $idx => $qText) {
                $ans = trim((string) ($validated['answers'][$idx] ?? ''));
                if ($ans !== '') {
                    $pairedAnswers[] = ['question' => $qText, 'answer' => $ans];
                }
            }

            CommunityJoinRequest::updateOrCreate(
                ['community_id' => $community->id, 'user_id' => $user->id],
                [
                    'status' => 'pending',
                    'answers' => ! empty($pairedAnswers) ? $pairedAnswers : null,
                    'note' => trim((string) ($validated['note'] ?? '')) ?: null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'rejection_reason' => null,
                ]
            );

            $msg = 'Permintaan bergabung terkirim. Menunggu persetujuan pengurus.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'joined' => false,
                    'pending' => true,
                    'members_count' => $community->members_count,
                    'message' => $msg,
                ]);
            }

            return back()->with('success', $msg);
        }

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);
        $community->increment('members_count');
        $community->joinRequests()->where('user_id', $user->id)->delete();

        $message = 'Kamu bergabung dengan '.$community->name;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'joined' => true,
                'pending' => false,
                'members_count' => $community->fresh()->members_count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Cancel the authenticated user's own pending join request.
     */
    public function cancelJoinRequest(Request $request, Community $community): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $community->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->delete();

        return back()->with('success', 'Permintaan bergabung dibatalkan.');
    }

    /**
     * Promote a member to moderator / demote a moderator (owner/admin).
     */
    public function toggleModerator(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isAdmin($auth->id)) {
            abort(403);
        }

        if ($user->id === $community->owner_id) {
            return back()->with('error', 'Pemilik tidak bisa diubah rolenya.');
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $community->canManageRole($auth->id, $member->role)) {
            abort(403);
        }

        $newRole = $member->role === 'mod' ? 'member' : 'mod';
        $member->update(['role' => $newRole]);

        $community->recordAudit(
            $auth->id,
            'member.role_updated',
            $newRole === 'mod' ? 'Mengangkat '.$user->name.' menjadi Moderator' : 'Mencopot '.$user->name.' dari Moderator',
            $user->id,
            ['role' => $newRole]
        );

        return back()->with('success', $newRole === 'mod'
            ? $user->name.' diangkat menjadi moderator.'
            : $user->name.' dicopot dari moderator.');
    }
}
