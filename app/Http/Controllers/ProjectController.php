<?php

namespace App\Http\Controllers;

use App\Events\CommentPosted;
use App\Events\ProjectPublished;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectBookmark;
use App\Models\ProjectComment;
use App\Models\ProjectRepost;
use App\Models\ProjectVote;
use App\Models\User;
use App\Notifications\ProjectRepostedNotification;
use App\Notifications\ProjectTrendingNotification;
use App\Notifications\ProjectUploadedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display project showcase feed.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'trend'); // 'trend', 'terbaru', 'populer', 'prototype'
        $categorySlug = $request->query('kategori');
        $techFilter = $request->query('tech');
        $search = $request->query('q') ?? $request->query('search');

        $query = Project::with(['user', 'category'])
            ->visibleTo(Auth::id())
            ->search($search);

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($techFilter) {
            $query->where('tech_stacks', 'like', "%{$techFilter}%");
        }

        // Apply Tab Sorting
        switch ($tab) {
            case 'following':
                $followingIds = Auth::check() ? Auth::user()->followingIds() : [];
                $query->whereIn('user_id', $followingIds)->recent();
                break;
            case 'prototype':
                $query->whereNotNull('prototype_url')->trending();
                break;
            case 'terbaru':
                $query->recent();
                break;
            case 'populer':
                $query->popular();
                break;
            case 'trend':
            default:
                $query->trending();
                break;
        }

        $projects = $query->paginate(12)->withQueryString();

        $categories = Category::withCount('projects')->get();

        // 3 focused secondary sidebar widgets
        // 1. Trending technologies
        $trendingTech = [
            'Laravel', 'Vue.js', 'React', 'TailwindCSS', 'Python',
            'TypeScript', 'Next.js', 'Go', 'Flutter', 'Docker',
        ];

        // 2. Trending projects today (top by score in the last 24h)
        $trendingToday = Project::with(['user', 'category'])
            ->visibleTo(Auth::id())
            ->where('created_at', '>=', now()->subDay())
            ->orderByDesc('score')
            ->take(5)
            ->get();

        // Fallback: if not enough today, fill from the past week
        if ($trendingToday->count() < 5) {
            $extra = Project::with(['user', 'category'])
                ->visibleTo(Auth::id())
                ->where('created_at', '<', now()->subDay())
                ->orderByDesc('score')
                ->take(5 - $trendingToday->count())
                ->get();
            $trendingToday = $trendingToday->concat($extra);
        }

        // 2b. Hot hashtags — most-used tech tags across visible projects
        $tagCounts = [];
        Project::query()
            ->visibleTo(Auth::id())
            ->select('tech_stacks')
            ->chunk(500, function ($rows) use (&$tagCounts) {
                foreach ($rows as $row) {
                    foreach ((array) $row->tech_stacks as $tag) {
                        $tag = trim((string) $tag);
                        if ($tag === '') {
                            continue;
                        }
                        $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                    }
                }
            });
        arsort($tagCounts);
        $hotHashtags = array_slice($tagCounts, 0, 8, true);

        // 3. Suggested developers to follow (not already followed, not self)
        $suggestedDevelopers = collect();
        if (Auth::check()) {
            $authUser = Auth::user();
            $followingIds = $authUser->followingIds();
            $suggestedDevelopers = User::where('id', '!=', $authUser->id)
                ->whereNotIn('id', $followingIds)
                ->orderByDesc('reputation_points')
                ->take(3)
                ->get();
        }

        // 4. Recent Discussions
        $recentReviews = ProjectComment::with(['user', 'project'])
            ->whereHas('project')
            ->latest()
            ->take(3)
            ->get();

        $userId = Auth::id();
        $userBookmarkedIds = $userId
            ? ProjectBookmark::where('user_id', $userId)->pluck('project_id')->toArray()
            : [];

        $userVotes = $userId
            ? ProjectVote::where('user_id', $userId)->pluck('type', 'project_id')->toArray()
            : [];

        $userRepostedIds = $userId
            ? ProjectRepost::where('user_id', $userId)->pluck('project_id')->toArray()
            : [];

        return view('projects.index', compact(
            'projects',
            'categories',
            'trendingToday',
            'hotHashtags',
            'trendingTech',
            'recentReviews',
            'userBookmarkedIds',
            'userVotes',
            'userRepostedIds',
            'tab',
            'categorySlug',
            'techFilter',
            'search',
            'suggestedDevelopers'
        ));
    }

    /**
     * Return the latest published projects as JSON (for real-time feed pulse).
     */
    public function latest(Request $request): JsonResponse
    {
        $since = $request->query('since');

        $query = Project::with(['user', 'category'])->recent();

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $projects = $query->take(20)->get()->map(fn (Project $p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'tagline' => $p->tagline,
            'thumbnail' => $p->thumbnail,
            'score' => $p->score,
            'comments_count' => $p->comments_count,
            'views_count' => $p->views_count,
            'is_new' => $p->created_at->diffInHours(now()) < 24,
            'author' => $p->user?->name,
            'username' => $p->user?->username,
            'avatar' => $p->user?->avatar,
            'category' => $p->category?->name,
            'tech_stacks' => array_slice($p->tech_stacks ?? [], 0, 5),
            'created_at' => $p->created_at->diffForHumans(),
            'created_iso' => $p->created_at->toIso8601String(),
            'url' => route('projects.show', $p->slug),
        ]);

        return response()->json([
            'count' => $projects->count(),
            'server_time' => now()->toIso8601String(),
            'projects' => $projects,
        ]);
    }

    /**
     * Display a single project detail.
     */
    public function show(string $slug, Request $request): View
    {
        $project = Project::with(['user', 'category', 'comments.user', 'repostedFrom.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Private projects are only visible to their owner.
        if ($project->isPrivate() && $project->user_id !== Auth::id()) {
            abort(404);
        }

        // Session-based view counting (prevents counting on every refresh)
        $viewKey = 'viewed_project_'.$project->id;
        if (! $request->session()->has($viewKey)) {
            $project->increment('views_count');
            $request->session()->put($viewKey, true);
        }

        $categories = Category::withCount('projects')->get();

        $relatedProjects = Project::with(['user', 'category'])
            ->where('category_id', $project->category_id)
            ->where('id', '!=', $project->id)
            ->orderByDesc('score')
            ->take(3)
            ->get();

        $userId = Auth::id();
        $userVote = $userId ? $project->getUserVoteType($userId) : null;
        $isBookmarked = $userId
            ? ProjectBookmark::where('project_id', $project->id)->where('user_id', $userId)->exists()
            : false;

        $userReposted = $userId
            ? ProjectRepost::where('project_id', $project->id)->where('user_id', $userId)->exists()
            : false;

        return view('projects.show', compact('project', 'categories', 'relatedProjects', 'userVote', 'isBookmarked', 'userReposted'));
    }

    /**
     * Show form to upload/submit a new coding project.
     */
    public function create(): View
    {
        $categories = Category::all();

        return view('projects.create', compact('categories'));
    }

    /**
     * Store new developer project.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'tagline' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:idea,prototype,beta,production,archived'],
            'visibility' => ['nullable', 'in:public,private'],
            'tech_stacks' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'learnings' => ['nullable', 'string'],
            'setup_instructions' => ['nullable', 'string'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'prototype_url' => ['nullable', 'url', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'thumbnail_url' => ['nullable', 'url', 'max:255'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk memamerkan karya.');
        }

        // Process tech stacks tags into array
        $techArray = array_values(array_filter(array_map('trim', explode(',', $validated['tech_stacks']))));

        // Handle thumbnail: file upload OR external image URL OR default
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $thumbnailPath = '/storage/'.$path;
        } elseif (! empty($validated['thumbnail_url'])) {
            $thumbnailPath = $validated['thumbnail_url'];
        } else {
            $thumbnailPath = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';
        }

        // Handle additional gallery images (multiple uploads).
        $galleryPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $galleryPaths[] = '/storage/'.$image->store('project-images', 'public');
                }
            }
        }

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $count = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $project = Project::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'],
            'project_type' => $validated['project_type'] ?? 'web',
            'status' => $validated['status'] ?? 'beta',
            'visibility' => $validated['visibility'] ?? 'public',
            'title' => $validated['title'],
            'slug' => $slug,
            'tagline' => $validated['tagline'],
            'description' => $validated['description'] ?? '',
            'challenges' => $validated['challenges'] ?? null,
            'learnings' => $validated['learnings'] ?? null,
            'setup_instructions' => $validated['setup_instructions'] ?? null,
            'thumbnail' => $thumbnailPath,
            'images' => ! empty($galleryPaths) ? $galleryPaths : null,
            'demo_url' => $validated['demo_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'prototype_url' => $validated['prototype_url'] ?? null,
            'tech_stacks' => $techArray,
            'upvotes_count' => 1,
            'downvotes_count' => 0,
            'score' => 1,
            'comments_count' => 0,
            'views_count' => 0,
            'is_featured' => false,
        ]);

        // Auto-upvote by creator
        ProjectVote::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'type' => 'up',
        ]);

        // Increment category count
        $project->category->increment('projects_count');

        // Notify followers about the new project + broadcast to live feed.
        $project->load('user');
        $followers = $user->followers;
        foreach ($followers as $follower) {
            $follower->notify(new ProjectUploadedNotification($project));
        }
        ProjectPublished::dispatch($project);

        return redirect()->route('projects.show', $project->slug)
            ->with('success', 'Project codingan kamu berhasil dipamerkan dan tampil di feed!');
    }

    /**
     * AJAX Like endpoint (like / unlike). Likes only — no dislikes.
     */
    public function vote(Request $request, Project $project): JsonResponse
    {
        $userId = Auth::id();

        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isAuthor = ($project->user_id === $userId);

        $existingVote = ProjectVote::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            // Toggle off (unlike).
            $existingVote->delete();
            $project->decrement('upvotes_count');
            $project->decrement('score');
            if (! $isAuthor && $project->user) {
                $project->user->decrement('reputation_points', 5);
            }
            $currentVoteType = null;
            $liked = false;
        } else {
            // New like.
            ProjectVote::create([
                'project_id' => $project->id,
                'user_id' => $userId,
                'ip_address' => $request->ip(),
                'type' => 'up',
            ]);
            $project->increment('upvotes_count');
            $project->increment('score');
            if (! $isAuthor && $project->user) {
                $project->user->increment('reputation_points', 5);
            }
            $currentVoteType = 'up';
            $liked = true;
        }

        $project->refresh();

        // Notify author when their project crosses a "trending" milestone.
        if (
            $liked
            && ! $isAuthor
            && $project->user
            && $project->score >= 50
            && $project->score - 1 < 50
        ) {
            $project->user->notify(new ProjectTrendingNotification($project));
        }

        return response()->json([
            'success' => true,
            'score' => $project->score,
            'upvotes' => $project->upvotes_count,
            'downvotes' => 0,
            'user_vote' => $currentVoteType,
        ]);
    }

    /**
     * Store comment on a project.
     */
    public function comment(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $comment = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'upvotes_count' => 0,
        ]);

        $project->increment('comments_count');

        $comment->load('user');
        CommentPosted::dispatch($comment);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'author' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
                'comments_count' => $project->comments_count,
            ]);
        }

        return back()->with('success', 'Komentar dan ulasan kamu berhasil dikirim!');
    }

    /**
     * AJAX Toggle bookmark on a project.
     */
    public function bookmark(Request $request, Project $project): JsonResponse
    {
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $bookmark = ProjectBookmark::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $isBookmarked = false;
        } else {
            ProjectBookmark::create([
                'project_id' => $project->id,
                'user_id' => $userId,
                'ip_address' => $request->ip(),
            ]);
            $isBookmarked = true;
        }

        return response()->json([
            'success' => true,
            'bookmarked' => $isBookmarked,
        ]);
    }

    /**
     * Display user's saved/bookmarked projects collection.
     */
    public function bookmarks(Request $request): View
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(401);
        }

        $projectIds = ProjectBookmark::where('user_id', $userId)->pluck('project_id');

        $projects = Project::with(['user', 'category'])
            ->whereIn('id', $projectIds)
            ->latest()
            ->paginate(12);

        $categories = Category::withCount('projects')->get();
        $userBookmarkedIds = $projectIds->toArray();
        $userVotes = ProjectVote::where('user_id', $userId)->pluck('type', 'project_id')->toArray();

        return view('projects.bookmarks', compact('projects', 'categories', 'userBookmarkedIds', 'userVotes'));
    }

    /**
     * Repost another developer's project to your own feed.
     * A repost is a lightweight share (like a retweet): it references the
     * original project instead of cloning it, and is always public.
     */
    public function repost(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        if ($project->user_id === $user->id) {
            $message = 'Kamu tidak bisa repost project sendiri.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        // The original must be public to be reposted.
        if ($project->isPrivate()) {
            $message = 'Project ini private dan tidak bisa di-repost.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return back()->with('error', $message);
        }

        // Already reposted? Treat as un-repost (toggle).
        $existing = ProjectRepost::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $project->decrement('reposts_count');
            $reposted = false;
            $message = 'Repost dibatalkan.';
        } else {
            ProjectRepost::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);
            $project->increment('reposts_count');
            $reposted = true;
            $message = 'Project berhasil di-repost ke profilmu!';

            // Notify the original author.
            if ($project->user) {
                $project->user->notify(new ProjectRepostedNotification($project, $user));
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'reposted' => $reposted,
                'reposts_count' => $project->fresh()->reposts_count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Toggle pinning a project to the top of the owner's profile (max 3).
     */
    public function togglePin(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $project->user_id !== $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            abort(403);
        }

        if (! $project->is_pinned) {
            $pinnedCount = $user->pinnedProjects()->count();
            if ($pinnedCount >= 3) {
                $message = 'Maksimal 3 project yang bisa disematkan.';
                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->with('error', $message);
            }
        }

        $project->update(['is_pinned' => ! $project->is_pinned]);

        $message = $project->is_pinned ? 'Project disematkan ke profil.' : 'Project dilepas dari sematan.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_pinned' => $project->is_pinned,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
