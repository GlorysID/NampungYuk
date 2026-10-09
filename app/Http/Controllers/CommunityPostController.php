<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityPostController extends Controller
{
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
                    $images[] = '/storage/'.$img->store('community-posts', 'public');
                }
            }
        }

        $community->posts()->create([
            'user_id' => $user->id,
            'type' => $type,
            'content' => $data['content'],
            'images' => ! empty($images) ? $images : null,
        ]);

        $community->increment('posts_count');

        return back()->with('success', 'Postingan terkirim!');
    }

    /**
     * Vote on a community post (AJAX).
     */
    public function vote(Request $request, CommunityPost $post): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $type = $request->validate(['type' => ['required', 'in:up,down']])['type'];

        $existing = CommunityPostVote::where('community_post_id', $post->id)
            ->where('user_id', $user->id)->first();

        $current = null;
        if ($existing) {
            if ($existing->type === $type) {
                $existing->delete();
                $type === 'up' ? $post->decrement('upvotes_count') : $post->decrement('downvotes_count');
                $post->decrement('score', $type === 'up' ? 1 : -1);
                $current = null;
            } else {
                $existing->update(['type' => $type]);
                if ($type === 'up') {
                    $post->increment('upvotes_count');
                    $post->decrement('downvotes_count');
                    $post->increment('score', 2);
                } else {
                    $post->increment('downvotes_count');
                    $post->decrement('upvotes_count');
                    $post->decrement('score', 2);
                }
                $current = $type;
            }
        } else {
            CommunityPostVote::create([
                'community_post_id' => $post->id,
                'user_id' => $user->id,
                'type' => $type,
            ]);
            $type === 'up' ? $post->increment('upvotes_count') : $post->increment('downvotes_count');
            $post->increment('score', $type === 'up' ? 1 : -1);
            $current = $type;
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
        $user = Auth::user();
        if (! $user) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
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
     * Delete a post (owner/mod only).
     */
    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = Auth::user();
        $community = $post->community;
        if (! $user || ! $community->isModerator($user->id)) {
            abort(403);
        }

        $post->delete();
        $community->decrement('posts_count');

        return back()->with('success', 'Postingan dihapus.');
    }
}
