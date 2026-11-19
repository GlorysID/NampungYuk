<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCommunitySettingsRequest;
use App\Models\Community;
use App\Models\CommunityBan;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\CommunityMembershipNotification;
use App\Notifications\CommunityPostNotification;
use App\Services\MediaOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityManagementController extends Controller
{
    public function __construct(private MediaOptimizer $media) {}

    /**
     * Community management workspace (Owner, Admin, Moderator).
     */
    public function index(Request $request, Community $community): View
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isModerator($auth->id)) {
            abort(403);
        }

        $myRole = $community->roleOf($auth->id);
        $isOwner = $community->isOwner($auth->id);
        $isAdmin = $community->isAdmin($auth->id);

        $tab = $request->query('tab', 'antrian');
        if (! in_array($tab, ['antrian', 'anggota', 'pengaturan', 'log'], true)) {
            $tab = 'antrian';
        }
        if ($tab === 'pengaturan' && ! $isAdmin) {
            abort(403);
        }

        $pendingRequests = $community->pendingJoinRequests()
            ->with('user:id,name,username,avatar,bio')
            ->latest()
            ->get();

        $pendingPosts = $community->pendingPosts()
            ->with('user:id,name,username,avatar')
            ->latest()
            ->get();

        $search = trim((string) $request->query('q', ''));
        $roleFilter = (string) $request->query('role', '');

        $members = $community->memberUsers()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.username', 'like', "%{$search}%");
                });
            })
            ->when(in_array($roleFilter, ['owner', 'admin', 'mod', 'member'], true), fn ($q) => $q->where('community_members.role', $roleFilter))
            ->orderByRaw("CASE community_members.role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 WHEN 'mod' THEN 2 ELSE 3 END")
            ->orderBy('users.name')
            ->paginate(20)
            ->withQueryString();

        $bans = $community->bans()
            ->with(['user:id,name,username,avatar', 'banner:id,name,username'])
            ->latest()
            ->get();

        $auditLogs = $community->auditLogs()
            ->with(['actor:id,name,username,avatar', 'targetUser:id,name,username'])
            ->latest('created_at')
            ->take(50)
            ->get();

        $adminCandidates = $isOwner
            ? $community->memberUsers()->where('community_members.role', 'admin')->orderBy('users.name')->get()
            : collect();

        return view('communities.manage', compact(
            'community',
            'tab',
            'myRole',
            'isOwner',
            'isAdmin',
            'pendingRequests',
            'pendingPosts',
            'members',
            'bans',
            'auditLogs',
            'adminCandidates',
            'search',
            'roleFilter'
        ));
    }

    /**
     * Update community settings, media, policies, questions, and structured rules (Owner/Admin).
     */
    public function updateSettings(UpdateCommunitySettingsRequest $request, Community $community): RedirectResponse
    {
        $auth = Auth::user();
        $data = $request->validated();

        $visibility = $data['visibility'];
        $joinPolicy = $visibility === 'private' ? 'approval' : $data['join_policy'];

        $joinQuestions = collect($data['join_questions'] ?? [])
            ->map(fn ($q) => trim((string) $q))
            ->filter(fn ($q) => $q !== '')
            ->take(3)
            ->values()
            ->all();

        $rulesItems = collect($data['rules_items'] ?? [])
            ->map(function ($item) {
                $title = trim((string) ($item['title'] ?? ''));
                $desc = trim((string) ($item['description'] ?? ''));

                return [
                    'title' => $title,
                    'description' => $desc !== '' ? $desc : null,
                ];
            })
            ->filter(fn ($item) => $item['title'] !== '')
            ->take(10)
            ->values()
            ->all();

        $legacyRules = ! empty($rulesItems)
            ? collect($rulesItems)->map(fn ($r, $idx) => ($idx + 1).'. '.$r['title'].($r['description'] ? ' — '.$r['description'] : ''))->implode("\n")
            : null;

        $iconValue = $community->icon;
        if ($request->boolean('remove_icon')) {
            $this->media->deleteStoredFile($community->icon);
            $iconValue = null;
        }
        if ($request->hasFile('icon_file')) {
            $this->media->deleteStoredFile($community->icon);
            $iconValue = '/storage/'.$this->media->storeImage($request->file('icon_file'), 'community-icons', 'avatar');
        } elseif (! empty($data['icon'])) {
            if ($community->icon && $community->icon !== $data['icon']) {
                $this->media->deleteStoredFile($community->icon);
            }
            $iconValue = $data['icon'];
        }

        $coverValue = $community->cover;
        if ($request->boolean('remove_cover')) {
            $this->media->deleteStoredFile($community->cover);
            $coverValue = null;
        }
        if ($request->hasFile('cover_file')) {
            $this->media->deleteStoredFile($community->cover);
            $coverValue = '/storage/'.$this->media->storeImage($request->file('cover_file'), 'community-covers', 'thumb');
        }

        $community->update([
            'name' => $data['name'],
            'topic' => $data['topic'] ?? null,
            'location' => $data['location'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'description' => $data['description'] ?? null,
            'welcome_message' => $data['welcome_message'] ?? null,
            'visibility' => $visibility,
            'join_policy' => $joinPolicy,
            'post_policy' => $data['post_policy'],
            'allow_member_comments' => $request->boolean('allow_member_comments'),
            'allow_member_images' => $request->boolean('allow_member_images'),
            'join_questions' => ! empty($joinQuestions) ? $joinQuestions : null,
            'rules_items' => ! empty($rulesItems) ? $rulesItems : null,
            'rules' => $legacyRules,
            'icon' => $iconValue,
            'cover' => $coverValue,
        ]);

        $community->recordAudit($auth?->id, 'settings.updated', 'Memperbarui pengaturan komunitas');

        return redirect()
            ->route('communities.manage.index', ['community' => $community->slug, 'tab' => 'pengaturan'])
            ->with('success', 'Pengaturan komunitas berhasil disimpan.');
    }

    /**
     * Approve a pending membership request (Owner/Admin/Mod).
     */
    public function approveJoinRequest(Request $request, Community $community, CommunityJoinRequest $joinRequest): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isModerator($auth->id)) {
            abort(403);
        }

        if ((int) $joinRequest->community_id !== (int) $community->id || $joinRequest->status !== 'pending') {
            abort(404);
        }

        DB::transaction(function () use ($community, $joinRequest, $auth) {
            $joinRequest->update([
                'status' => 'approved',
                'reviewed_by' => $auth->id,
                'reviewed_at' => now(),
            ]);

            $member = CommunityMember::firstOrCreate(
                ['community_id' => $community->id, 'user_id' => $joinRequest->user_id],
                ['role' => 'member']
            );

            if ($member->wasRecentlyCreated) {
                $community->increment('members_count');
            }
        });

        $joinRequest->load('user');
        if ($joinRequest->user) {
            $joinRequest->user->notify(new CommunityMembershipNotification(
                $community,
                'Permintaan bergabungmu ke '.$community->name.' telah disetujui.',
                $auth,
            ));
        }

        $community->recordAudit(
            $auth->id,
            'join.approved',
            'Menyetujui permintaan bergabung '.($joinRequest->user?->name ?? 'pengguna'),
            $joinRequest->user_id
        );

        return back()->with('success', 'Permintaan bergabung disetujui.');
    }

    /**
     * Reject a pending membership request (Owner/Admin/Mod).
     */
    public function rejectJoinRequest(Request $request, Community $community, CommunityJoinRequest $joinRequest): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isModerator($auth->id)) {
            abort(403);
        }

        if ((int) $joinRequest->community_id !== (int) $community->id || $joinRequest->status !== 'pending') {
            abort(404);
        }

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = trim((string) ($data['rejection_reason'] ?? '')) ?: null;

        $joinRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $auth->id,
            'reviewed_at' => now(),
        ]);

        $joinRequest->load('user');
        if ($joinRequest->user) {
            $msg = 'Permintaan bergabungmu ke '.$community->name.' ditolak.'
                .($reason ? ' Alasan: '.$reason : '');
            $joinRequest->user->notify(new CommunityMembershipNotification($community, $msg, $auth));
        }

        $community->recordAudit(
            $auth->id,
            'join.rejected',
            'Menolak permintaan bergabung '.($joinRequest->user?->name ?? 'pengguna'),
            $joinRequest->user_id,
            $reason ? ['reason' => $reason] : []
        );

        return back()->with('success', 'Permintaan bergabung ditolak.');
    }

    /**
     * Approve a pending community post (Owner/Admin/Mod).
     */
    public function approvePost(Request $request, Community $community, CommunityPost $post): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isModerator($auth->id)) {
            abort(403);
        }

        if ((int) $post->community_id !== (int) $community->id || $post->status !== 'pending') {
            abort(404);
        }

        $post->update([
            'status' => 'approved',
            'reviewed_by' => $auth->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $community->increment('posts_count');

        $post->load(['user', 'community']);
        if ($post->user && (int) $post->user_id !== (int) $auth->id) {
            $post->user->notify(new CommunityMembershipNotification(
                $community,
                'Postinganmu di '.$community->name.' telah disetujui dan diterbitkan.',
                $auth,
                route('communities.show', $community->slug).'#post-'.$post->id
            ));
        }

        $community->memberUsers()
            ->where('users.id', '!=', $post->user_id)
            ->get()
            ->each(fn ($member) => $member->notify(new CommunityPostNotification($post)));

        $community->recordAudit(
            $auth->id,
            'post.approved',
            'Menyetujui postingan oleh '.($post->user?->name ?? 'anggota'),
            $post->user_id,
            ['post_id' => $post->id]
        );

        return back()->with('success', 'Postingan disetujui dan diterbitkan.');
    }

    /**
     * Reject a pending community post (Owner/Admin/Mod).
     */
    public function rejectPost(Request $request, Community $community, CommunityPost $post): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isModerator($auth->id)) {
            abort(403);
        }

        if ((int) $post->community_id !== (int) $community->id || $post->status !== 'pending') {
            abort(404);
        }

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = trim((string) ($data['rejection_reason'] ?? '')) ?: null;

        $post->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $auth->id,
            'reviewed_at' => now(),
        ]);

        $post->load('user');
        if ($post->user) {
            $msg = 'Postinganmu di '.$community->name.' ditolak oleh pengurus.'
                .($reason ? ' Alasan: '.$reason : '');
            $post->user->notify(new CommunityMembershipNotification($community, $msg, $auth));
        }

        $community->recordAudit(
            $auth->id,
            'post.rejected',
            'Menolak postingan oleh '.($post->user?->name ?? 'anggota'),
            $post->user_id,
            array_filter(['post_id' => $post->id, 'reason' => $reason])
        );

        return back()->with('success', 'Postingan ditolak.');
    }

    /**
     * Update member role (Owner -> admin/mod/member; Admin -> mod/member).
     */
    public function updateMemberRole(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isAdmin($auth->id)) {
            abort(403);
        }

        $data = $request->validate([
            'role' => ['required', 'in:admin,mod,member'],
        ]);

        if ((int) $user->id === (int) $community->owner_id) {
            return back()->with('error', 'Pemilik komunitas tidak bisa diubah perannya.');
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $community->canManageRole($auth->id, $member->role, $data['role'])) {
            abort(403);
        }

        $member->update(['role' => $data['role']]);

        $roleLabel = CommunityMember::labelForRole($data['role']);
        $user->notify(new CommunityMembershipNotification(
            $community,
            'Peranmu di '.$community->name.' diubah menjadi '.$roleLabel.'.',
            $auth
        ));

        $community->recordAudit(
            $auth->id,
            'member.role_updated',
            'Mengubah peran '.$user->name.' menjadi '.$roleLabel,
            $user->id,
            ['role' => $data['role']]
        );

        return back()->with('success', 'Peran '.$user->name.' diperbarui menjadi '.$roleLabel.'.');
    }

    /**
     * Mute or unmute a member inside this community.
     */
    public function muteMember(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isModerator($auth->id)) {
            abort(403);
        }

        if ((int) $user->id === (int) $community->owner_id) {
            return back()->with('error', 'Pemilik komunitas tidak dapat dibisukan.');
        }

        $data = $request->validate([
            'duration' => ['required', 'in:24h,7d,unmute'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $actorRole = $community->roleOf($auth->id);
        if ($actorRole === 'mod' && $member->role !== 'member') {
            abort(403);
        }
        if ($actorRole === 'admin' && $member->role === 'admin') {
            abort(403);
        }

        if ($data['duration'] === 'unmute') {
            $member->update([
                'muted_until' => null,
                'mute_reason' => null,
            ]);

            $community->recordAudit(
                $auth->id,
                'member.unmuted',
                'Melepas pembisuan '.$user->name,
                $user->id
            );

            return back()->with('success', 'Pembisuan '.$user->name.' dilepas.');
        }

        $until = $data['duration'] === '7d' ? now()->addDays(7) : now()->addDay();
        $reason = trim((string) ($data['reason'] ?? '')) ?: null;

        $member->update([
            'muted_until' => $until,
            'mute_reason' => $reason,
        ]);

        $durationText = $data['duration'] === '7d' ? '7 hari' : '24 jam';
        $user->notify(new CommunityMembershipNotification(
            $community,
            'Kamu dibisukan di '.$community->name.' selama '.$durationText.'.',
            $auth
        ));

        $community->recordAudit(
            $auth->id,
            'member.muted',
            'Membisukan '.$user->name.' selama '.$durationText,
            $user->id,
            array_filter(['duration' => $data['duration'], 'reason' => $reason])
        );

        return back()->with('success', $user->name.' dibisukan selama '.$durationText.'.');
    }

    /**
     * Kick a member out of the community (Owner/Admin).
     */
    public function kickMember(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isAdmin($auth->id)) {
            abort(403);
        }

        if ((int) $user->id === (int) $community->owner_id) {
            return back()->with('error', 'Pemilik komunitas tidak dapat dikeluarkan.');
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $community->isOwner($auth->id) && $member->role === 'admin') {
            abort(403);
        }

        $member->delete();
        if ($community->members_count > 0) {
            $community->decrement('members_count');
        }

        $user->notify(new CommunityMembershipNotification(
            $community,
            'Kamu dikeluarkan dari komunitas '.$community->name.'.',
            $auth
        ));

        $community->recordAudit(
            $auth->id,
            'member.kicked',
            'Mengeluarkan '.$user->name.' dari komunitas',
            $user->id
        );

        return back()->with('success', $user->name.' dikeluarkan dari komunitas.');
    }

    /**
     * Ban a user from the community (Owner/Admin).
     */
    public function banMember(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isAdmin($auth->id)) {
            abort(403);
        }

        if ((int) $user->id === (int) $community->owner_id) {
            return back()->with('error', 'Pemilik komunitas tidak dapat diblokir.');
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if ($member && ! $community->isOwner($auth->id) && $member->role === 'admin') {
            abort(403);
        }

        $reason = trim((string) ($data['reason'] ?? '')) ?: null;

        DB::transaction(function () use ($community, $user, $auth, $member, $reason) {
            if ($member) {
                $member->delete();
                if ($community->members_count > 0) {
                    $community->decrement('members_count');
                }
            }

            $community->joinRequests()->where('user_id', $user->id)->delete();

            CommunityBan::updateOrCreate(
                ['community_id' => $community->id, 'user_id' => $user->id],
                ['banned_by' => $auth->id, 'reason' => $reason]
            );
        });

        $user->notify(new CommunityMembershipNotification(
            $community,
            'Kamu diblokir dari komunitas '.$community->name.'.'.($reason ? ' Alasan: '.$reason : ''),
            $auth
        ));

        $community->recordAudit(
            $auth->id,
            'member.banned',
            'Memblokir '.$user->name.' dari komunitas',
            $user->id,
            $reason ? ['reason' => $reason] : []
        );

        return back()->with('success', $user->name.' diblokir dari komunitas.');
    }

    /**
     * Unban a user from the community (Owner/Admin).
     */
    public function unbanMember(Request $request, Community $community, User $user): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isAdmin($auth->id)) {
            abort(403);
        }

        CommunityBan::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->delete();

        $community->recordAudit(
            $auth->id,
            'member.unbanned',
            'Membuka blokir '.$user->name,
            $user->id
        );

        return back()->with('success', 'Blokir '.$user->name.' dibuka.');
    }

    /**
     * Transfer community ownership to an existing admin (Owner only).
     */
    public function transferOwnership(Request $request, Community $community): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isOwner($auth->id)) {
            abort(403);
        }

        $data = $request->validate([
            'new_owner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $newOwner = User::findOrFail($data['new_owner_id']);

        $targetMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $newOwner->id)
            ->where('role', 'admin')
            ->first();

        if (! $targetMember) {
            return back()->with('error', 'Kepemilikan hanya dapat dipindahkan ke anggota yang berstatus Admin.');
        }

        DB::transaction(function () use ($community, $auth, $newOwner, $targetMember) {
            $community->update(['owner_id' => $newOwner->id]);

            CommunityMember::updateOrCreate(
                ['community_id' => $community->id, 'user_id' => $auth->id],
                ['role' => 'admin']
            );

            $targetMember->update(['role' => 'owner']);
        });

        $newOwner->notify(new CommunityMembershipNotification(
            $community,
            'Kamu kini menjadi Pemilik komunitas '.$community->name.'.',
            $auth
        ));

        $community->recordAudit(
            $auth->id,
            'ownership.transferred',
            'Memindahkan kepemilikan komunitas kepada '.$newOwner->name,
            $newOwner->id
        );

        return redirect()
            ->route('communities.manage.index', ['community' => $community->slug, 'tab' => 'anggota'])
            ->with('success', 'Kepemilikan komunitas berhasil dipindahkan ke '.$newOwner->name.'.');
    }

    /**
     * Permanently delete the community and its media assets (Owner only).
     */
    public function destroy(Request $request, Community $community): RedirectResponse
    {
        $auth = Auth::user();
        if (! $auth || ! $community->isOwner($auth->id)) {
            abort(403);
        }

        $request->validate([
            'confirm_name' => ['required', 'string', Rule::in([$community->name])],
        ], [
            'confirm_name.in' => 'Nama konfirmasi harus sama persis dengan nama komunitas.',
        ]);

        $this->media->deleteStoredFile($community->icon);
        $this->media->deleteStoredFile($community->cover);

        $community->posts()->chunkById(100, function ($posts) {
            foreach ($posts as $post) {
                foreach ($post->images ?? [] as $img) {
                    $this->media->deleteStoredFile(is_string($img) ? $img : null);
                }
            }
        });

        $name = $community->name;
        $community->delete();

        return redirect()
            ->route('communities.index')
            ->with('success', 'Komunitas '.$name.' telah dihapus.');
    }
}
