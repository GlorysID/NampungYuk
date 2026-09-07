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
     * Display project showcase feed (Lahelu style).
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'trend'); // 'trend', 'terbaru', 'populer'
        $categorySlug = $request->query('kategori');
        $techFilter = $request->query('tech');
        $search = $request->query('q');

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

        $spotlightProject = Project::with(['user', 'category'])
            ->whereNotNull('thumbnail')
            ->orderByDesc('score')
            ->first();

        $categories = Category::withCount('projects')->get();

        $topDevelopers = User::whereNotNull('reputation_points')
            ->orderByDesc('reputation_points')
            ->take(5)
            ->get();

        $trendingTech = [
            'Laravel', 'Vue', 'React', 'TailwindCSS', 'Python',
            'TypeScript', 'Next.js', 'Go', 'Flutter', 'AI',
        ];

        $userId = Auth::id();
        $ip = $request->ip();

        $userBookmarkedIds = $userId
            ? ProjectBookmark::where('user_id', $userId)->pluck('project_id')->toArray()
            : ProjectBookmark::where('ip_address', $ip)->pluck('project_id')->toArray();

        $userVotes = $userId
            ? ProjectVote::where('user_id', $userId)->pluck('type', 'project_id')->toArray()
            : ProjectVote::where('ip_address', $ip)->pluck('type', 'project_id')->toArray();

        $recentDeploysQuery = Project::with(['user', 'category'])->latest();
        if ($request->filled('kategori')) {
            $recentDeploysQuery->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->kategori);
            });
        }
        $recentDeploys = $recentDeploysQuery->take(4)->get();

        $recentReviewsQuery = ProjectComment::with(['user', 'project'])->latest();
        if ($request->filled('kategori')) {
            $recentReviewsQuery->whereHas('project.category', function ($q) use ($request) {
                $q->where('slug', $request->kategori);
            });
        }
        $recentReviews = $recentReviewsQuery->take(3)->get();

        return view('projects.index', compact(
            'projects',
            'spotlightProject',
            'categories',
            'topDevelopers',
            'trendingTech',
            'recentDeploys',
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

        // Increment views count safely
        $project->increment('views_count');

        $categories = Category::withCount('projects')->get();

        $relatedProjects = Project::with('user')
            ->where('category_id', $project->category_id)
            ->where('id', '!=', $project->id)
            ->orderByDesc('score')
            ->take(4)
            ->get();

        $userVote = $project->getUserVoteType(Auth::id(), $request->ip());

        $userId = Auth::id();
        $ip = $request->ip();
        $isBookmarked = $userId
            ? ProjectBookmark::where('project_id', $project->id)->where('user_id', $userId)->exists()
            : ProjectBookmark::where('project_id', $project->id)->where('ip_address', $ip)->exists();

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
            'tech_stacks' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'prototype_url' => ['nullable', 'url', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
            'thumbnail_url' => ['nullable', 'url', 'max:255'],
            'guest_name' => ['nullable', 'string', 'max:60'],
        ]);

        // Process tech stacks tags into array
        $techArray = array_values(array_filter(array_map('trim', explode(',', $validated['tech_stacks']))));

        // Handle thumbnail: file upload OR external image URL OR default
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $thumbnailPath = asset('storage/'.$path);
        } elseif (! empty($validated['thumbnail_url'])) {
            $thumbnailPath = $validated['thumbnail_url'];
        } else {
            // Default developer aesthetic banner
            $thumbnailPath = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';
        }

        // Find or assign user
        $user = Auth::user();
        if (! $user) {
            $guestName = $validated['guest_name'] ?: 'Dev '.Str::random(4);
            $user = User::firstOrCreate(
                ['email' => Str::slug($guestName).'@guest.nampungyuk.id'],
                [
                    'name' => $guestName,
                    'username' => Str::slug($guestName).'_'.rand(100, 999),
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed='.urlencode($guestName),
                    'reputation_points' => 50,
                ]
            );
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
            'title' => $validated['title'],
            'slug' => $slug,
            'tagline' => $validated['tagline'],
            'description' => $validated['description'] ?? '',
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
            ->with('success', 'Project codingan kamu berhasil ditampung dan tampil di feed!');
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

        // Check existing vote
        $voteQuery = ProjectVote::where('project_id', $project->id);
        if ($userId) {
            $existingVote = $voteQuery->where('user_id', $userId)->first();
        } else {
            $existingVote = $voteQuery->where('ip_address', $ip)->first();
        }

        $currentVoteType = null;

        if ($existingVote) {
            if ($existingVote->type === $type) {
                // Remove vote (toggle off)
                $existingVote->delete();
                if ($type === 'up') {
                    $project->decrement('upvotes_count');
                    $project->decrement('score');
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
                } else {
                    $project->increment('downvotes_count');
                    $project->decrement('upvotes_count');
                    $project->decrement('score', 2);
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
                // Give reputation to author
                $project->user?->increment('reputation_points', 5);
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
            'guest_name' => ['nullable', 'string', 'max:50'],
        ]);

        $user = Auth::user();

        $comment = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : ($validated['guest_name'] ?: 'Anon Programmer'),
            'content' => $validated['content'],
            'upvotes_count' => 0,
        ]);

        $project->increment('comments_count');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'author' => $comment->authorName(),
                    'avatar' => $comment->authorAvatar(),
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
                'comments_count' => $project->comments_count,
            ]);
        }

        return back()->with('success', 'Komentar berhasil dikirim!');
    }

    /**
     * AJAX Toggle bookmark on a project.
     */
    public function bookmark(Request $request, Project $project): JsonResponse
    {
        $userId = Auth::id();
        $ip = $request->ip();

        $query = ProjectBookmark::where('project_id', $project->id);
        if ($userId) {
            $bookmark = $query->where('user_id', $userId)->first();
        } else {
            $bookmark = $query->where('ip_address', $ip)->first();
        }

        if ($bookmark) {
            $bookmark->delete();
            $isBookmarked = false;
        } else {
            ProjectBookmark::create([
                'project_id' => $project->id,
                'user_id' => $userId,
                'ip_address' => $ip,
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
        $ip = $request->ip();

        $bookmarkQuery = ProjectBookmark::query();
        if ($userId) {
            $projectIds = $bookmarkQuery->where('user_id', $userId)->pluck('project_id');
        } else {
            $projectIds = $bookmarkQuery->where('ip_address', $ip)->pluck('project_id');
        }

        $projects = Project::with(['user', 'category'])
            ->whereIn('id', $projectIds)
            ->latest()
            ->paginate(12);

        $categories = Category::withCount('projects')->get();
        $topDevelopers = User::whereNotNull('reputation_points')->orderByDesc('reputation_points')->take(5)->get();
        $trendingTech = ['Laravel', 'Vue', 'React', 'TailwindCSS', 'Python', 'TypeScript', 'Next.js', 'Go', 'Flutter', 'AI'];
        $userBookmarkedIds = $projectIds->toArray();

        $userVotes = $userId
            ? ProjectVote::where('user_id', $userId)->pluck('type', 'project_id')->toArray()
            : ProjectVote::where('ip_address', $ip)->pluck('type', 'project_id')->toArray();

        return view('projects.bookmarks', compact('projects', 'categories', 'topDevelopers', 'trendingTech', 'userBookmarkedIds', 'userVotes'));
    }
}
