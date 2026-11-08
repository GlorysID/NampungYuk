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

        if (! $community->hasMember($user->id)) {
            return back()->with('error', 'Kamu harus bergabung dulu untuk memposting.');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'in:discussion,question,announcement'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
        ]);

        // Only owner/mod can post announcements.
        $type = $data['type'] ?? 'discussion';
        if ($type === 'announcement' && ! $community->isModerator($user->id)) {
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

        $post = $community->posts()->create([
            'user_id' => $user->id,
            'type' => $type,
            'content' => $data['content'],
            'images' => ! empty($images) ? $images : null,
        ]);

        $community->increment('posts_count');

        // Notify all other members of the new post.
        $post->load('user');
        $community->memberUsers()
            ->where('users.id', '!=', $user->id)
            ->get()
            ->each(fn ($member) => $member->notify(new CommunityPostNotification($post)));

        return back()->with('success', 'Postingan terkirim!');
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

        if ($post->community?->isPrivate() && ! $post->community->hasMember($user->id) && ! $user->isAdmin()) {
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

        if ($post->community?->isPrivate() && ! $post->community->hasMember($user->id) && ! $user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:community_post_comments,id'],
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
            if ($parent && $parent->user_id && $parent->user_id !== $user->id) {
                $parent->user->notify(new CommunityCommentNotification($comment));
            }
        } elseif ($post->user && $post->user_id !== $user->id) {
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
     * Toggle pin on a post (owner/mod only).
     */
    public function togglePin(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = Auth::user();
        $community = $post->community;
        if (! $user || ! $community->isModerator($user->id)) {
            abort(403);
        }

        $post->update(['is_pinned' => ! $post->is_pinned]);

        return back()->with('success', $post->is_pinned ? 'Postingan disematkan.' : 'Sematan dilepas.');
    }

    /**
     * Mark a question post as answered (owner/mod only).
     */
    public function markAnswered(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = Auth::user();
        $community = $post->community;
        if (! $user || ! $community->isModerator($user->id)) {
            abort(403);
        }

        $post->update(['is_answered' => ! $post->is_answered]);

        return back()->with('success', $post->is_answered ? 'Ditandai terjawab.' : 'Tanda terjawab dilepas.');
    }

    /**
     * Delete a post (author, community owner/mod, or admin).
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

        $post->delete();

        if ($community && $community->posts_count > 0) {
            $community->decrement('posts_count');
        }

        return back()->with('success', 'Postingan dihapus.');
    }
}
