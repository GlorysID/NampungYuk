<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostVote;
use App\Models\User;
use App\Notifications\CommunityCommentNotification;
use App\Notifications\CommunityPostNotification;
use App\Services\MediaOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommunityPostController extends Controller
{
    public function __construct(private MediaOptimizer $media) {}

    /**
     * Create a post inside a community (members only).
     */
    public function store(Request $request, Community $community): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($community->isBanned($user->id)) {
            abort(403, 'Kamu diblokir dari komunitas ini.');
        }

        if (! $community->hasMember($user->id)) {
            return back()->with('error', 'Kamu harus bergabung dulu untuk memposting.');
        }

        $memberRecord = $community->memberRecord($user->id);
        if ($memberRecord && $memberRecord->isMuted()) {
            return back()->with('error', 'Akunmu sedang dibisukan hingga '.$memberRecord->muted_until->translatedFormat('d M Y H:i').'.');
        }

        $isMod = $community->isModerator($user->id);
        if ($community->post_policy === 'admins_only' && ! $isMod) {
            return back()->with('error', 'Hanya admin dan moderator yang dapat membuat postingan di komunitas ini.');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'in:discussion,question,announcement'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
        ]);

        if ($request->hasFile('images') && ! $community->allow_member_images && ! $isMod) {
            return back()->with('error', 'Komunitas ini menonaktifkan unggahan gambar untuk anggota biasa.');
        }

        // Only owner/admin/mod can post announcements.
        $type = $data['type'] ?? 'discussion';
        if ($type === 'announcement' && ! $isMod) {
            $type = 'discussion';
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                if ($img && $img->isValid()) {
                    $images[] = '/storage/'.$this->media->storeImage($img, 'community-posts', 'gallery');
                }
            }
        }

        $requiresApproval = $community->postsRequireApproval($user->id);
        $status = $requiresApproval ? 'pending' : 'approved';

        $post = $community->posts()->create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => $status,
            'content' => $data['content'],
            'images' => ! empty($images) ? $images : null,
        ]);

        if ($status === 'approved') {
            $community->increment('posts_count');

            // Notify all other members of the new post.
            $post->load('user');
            $community->memberUsers()
                ->where('users.id', '!=', $user->id)
                ->get()
                ->each(fn ($member) => $member->notify(new CommunityPostNotification($post)));

            return back()->with('success', 'Postingan terkirim!');
        }

        return back()->with('success', 'Postingan terkirim dan menunggu persetujuan pengurus.');
    }

    /**
     * Vote on a community post (AJAX).
     */
    public function vote(Request $request, CommunityPost $post): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $community = $post->community;
        if (! $community || $post->status !== 'approved' || $community->isBanned($user->id)) {
            abort(403);
        }

        if ($community->isPrivate() && ! $community->hasMember($user->id) && ! $user->isAdmin()) {
            abort(403);
        }

        $existing = CommunityPostVote::where('community_post_id', $post->id)
            ->where('user_id', $user->id)->first();

        if ($existing) {
            // Unlike.
            $existing->delete();
            $post->decrement('upvotes_count');
            $post->decrement('score');
            $current = null;
        } else {
            // Like.
            CommunityPostVote::create([
                'community_post_id' => $post->id,
                'user_id' => $user->id,
                'type' => 'up',
            ]);
            $post->increment('upvotes_count');
            $post->increment('score');
            $current = 'up';
        }

        $post->refresh();

        return response()->json([
            'success' => true,
            'score' => $post->score,
            'user_vote' => $current,
        ]);
    }

    /**
     * Comment on a community post.
     */
    public function comment(Request $request, CommunityPost $post): RedirectResponse|JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $community = $post->community;
        if (! $community || $post->status !== 'approved' || $community->isBanned($user->id)) {
            abort(403);
        }

        if ($community->isPrivate() && ! $community->hasMember($user->id) && ! $user->isAdmin()) {
            abort(403);
        }

        $isMod = $community->isModerator($user->id);

        if ($community->isMuted($user->id)) {
            abort(403, 'Akunmu sedang dibisukan di komunitas ini.');
        }

        if (! $community->allow_member_comments && ! $isMod) {
            abort(403, 'Komentar anggota dinonaktifkan di komunitas ini.');
        }

        if ($post->is_locked && ! $isMod) {
            abort(403, 'Komentar pada diskusi ini telah dikunci.');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => [
                'nullable',
                Rule::exists('community_post_comments', 'id')->where('community_post_id', $post->id),
            ],
        ]);

        $comment = CommunityPostComment::create([
            'community_post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
        ]);

        $post->increment('comments_count');

        // Notifications: reply -> parent comment author; top-level -> post author.
        $comment->load('user');
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parent = CommunityPostComment::find($parentId);
            if ($parent && $parent->user && $parent->user_id !== $user->id && $parent->user->wantsNotification('project_comment')) {
                $parent->user->notify(new CommunityCommentNotification($comment));
            }
        } elseif ($post->user && $post->user_id !== $user->id && $post->user->wantsNotification('project_comment')) {
            $post->user->notify(new CommunityCommentNotification($comment));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'author' => $user->name,
                    'username' => $user->username,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
                'comments_count' => $post->fresh()->comments_count,
            ]);
        }

        return back()->with('success', 'Komentar terkirim!');
    }

    /**
     * Toggle pin on a post (owner/admin/mod only).
     */
    public function togglePin(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = Auth::user();
        $community = $post->community;
        if (! $user || ! $community || ! $community->isModerator($user->id)) {
            abort(403);
        }

        $post->update(['is_pinned' => ! $post->is_pinned]);

        $community->recordAudit(
            $user->id,
            $post->is_pinned ? 'post.pinned' : 'post.unpinned',
            $post->is_pinned ? 'Menyematkan postingan #'.$post->id : 'Melepas sematan postingan #'.$post->id,
            $post->user_id,
            ['post_id' => $post->id]
        );

        return back()->with('success', $post->is_pinned ? 'Postingan disematkan.' : 'Sematan dilepas.');
    }

    /**
     * Toggle comment lock on a post (owner/admin/mod only).
     */
    public function toggleLock(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = Auth::user();
        $community = $post->community;
        if (! $user || ! $community || ! $community->isModerator($user->id)) {
            abort(403);
        }

        $post->update(['is_locked' => ! $post->is_locked]);

        $community->recordAudit(
            $user->id,
            $post->is_locked ? 'post.locked' : 'post.unlocked',
            $post->is_locked ? 'Mengunci komentar postingan #'.$post->id : 'Membuka komentar postingan #'.$post->id,
            $post->user_id,
            ['post_id' => $post->id]
        );

        return back()->with('success', $post->is_locked ? 'Komentar pada postingan dikunci.' : 'Komentar pada postingan dibuka kembali.');
    }

    /**
     * Mark a question post as answered (owner/admin/mod only).
     */
    public function markAnswered(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = Auth::user();
        $community = $post->community;
        if (! $user || ! $community || ! $community->isModerator($user->id)) {
            abort(403);
        }

        $post->update(['is_answered' => ! $post->is_answered]);

        return back()->with('success', $post->is_answered ? 'Ditandai terjawab.' : 'Tanda terjawab dilepas.');
    }

    /**
     * Delete a post (author, community owner/admin/mod, or platform admin).
     */
    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        $community = $post->community;
        $isAuthor = $user && (int) $post->user_id === (int) $user->id;
        $isAdmin = $user?->isAdmin() ?? false;
        $isMod = $user && $community && $community->isModerator($user->id);

        if (! $user || (! $isAuthor && ! $isAdmin && ! $isMod)) {
            abort(403);
        }

        $wasApproved = $post->status === 'approved';
        $authorId = $post->user_id;
        $postId = $post->id;

        foreach ($post->images ?? [] as $img) {
            $this->media->deleteStoredFile(is_string($img) ? $img : null);
        }

        $post->delete();

        if ($community && $wasApproved && $community->posts_count > 0) {
            $community->decrement('posts_count');
        }

        if ($community && $isMod && ! $isAuthor) {
            $community->recordAudit(
                $user->id,
                'post.deleted',
                'Menghapus postingan #'.$postId,
                $authorId,
                ['post_id' => $postId]
            );
        }

        return back()->with('success', 'Postingan dihapus.');
    }
}
