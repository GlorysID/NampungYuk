<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectBookmark;
use App\Models\ProjectComment;
use App\Models\ProjectVote;
use App\Models\User;
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

        // 2. Active Creators by reputation
        $topDevelopers = User::whereNotNull('reputation_points')
            ->where('reputation_points', '>', 0)
            ->orderByDesc('reputation_points')
            ->take(5)
            ->get();

        // 3. Recent Discussions
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

        return view('projects.index', compact(
            'projects',
            'categories',
            'topDevelopers',
            'trendingTech',
            'recentReviews',
            'userBookmarkedIds',
            'userVotes',
            'tab',
            'categorySlug',
            'techFilter',
            'search'
        ));
    }

    /**
     * Display a single project detail.
     */
    public function show(string $slug, Request $request): View
    {
        $project = Project::with(['user', 'category', 'comments.user'])
            ->where('slug', $slug)
            ->firstOrFail();

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

        return view('projects.show', compact('project', 'categories', 'relatedProjects', 'userVote', 'isBookmarked'));
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
            'title' => $validated['title'],
            'slug' => $slug,
            'tagline' => $validated['tagline'],
            'description' => $validated['description'] ?? '',
            'challenges' => $validated['challenges'] ?? null,
            'learnings' => $validated['learnings'] ?? null,
            'setup_instructions' => $validated['setup_instructions'] ?? null,
            'thumbnail' => $thumbnailPath,
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

        return redirect()->route('projects.show', $project->slug)
            ->with('success', 'Project codingan kamu berhasil dipamerkan dan tampil di feed!');
    }

    /**
     * AJAX Vote endpoint (upvote / downvote).
     */
    public function vote(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:up,down'],
        ]);

        $type = $validated['type'];
        $userId = Auth::id();
        $ip = $request->ip();

        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $existingVote = ProjectVote::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->first();

        $currentVoteType = null;
        $isAuthor = ($project->user_id === $userId);

        if ($existingVote) {
            if ($existingVote->type === $type) {
                // Remove vote (toggle off)
                $existingVote->delete();
                if ($type === 'up') {
                    $project->decrement('upvotes_count');
                    $project->decrement('score');
                    // Deduct reputation if upvote was canceled
                    if (! $isAuthor && $project->user) {
                        $project->user->decrement('reputation_points', 5);
                    }
                } else {
                    $project->decrement('downvotes_count');
                    $project->increment('score');
                }
                $currentVoteType = null;
            } else {
                // Switch vote (e.g. from down to up, or up to down)
                $existingVote->update(['type' => $type]);
                if ($type === 'up') {
                    $project->increment('upvotes_count');
                    $project->decrement('downvotes_count');
                    $project->increment('score', 2);
                    // Switched from down to up: reward reputation
                    if (! $isAuthor && $project->user) {
                        $project->user->increment('reputation_points', 5);
                    }
                } else {
                    $project->increment('downvotes_count');
                    $project->decrement('upvotes_count');
                    $project->decrement('score', 2);
                    // Switched from up to down: deduct reputation
                    if (! $isAuthor && $project->user) {
                        $project->user->decrement('reputation_points', 5);
                    }
                }
                $currentVoteType = $type;
            }
        } else {
            // New vote
            ProjectVote::create([
                'project_id' => $project->id,
                'user_id' => $userId,
                'ip_address' => $ip,
                'type' => $type,
            ]);

            if ($type === 'up') {
                $project->increment('upvotes_count');
                $project->increment('score');
                if (! $isAuthor && $project->user) {
                    $project->user->increment('reputation_points', 5);
                }
            } else {
                $project->increment('downvotes_count');
                $project->decrement('score');
            }

            $currentVoteType = $type;
        }

        $project->refresh();

        return response()->json([
            'success' => true,
            'score' => $project->score,
            'upvotes' => $project->upvotes_count,
            'downvotes' => $project->downvotes_count,
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
}
