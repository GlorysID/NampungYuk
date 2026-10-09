<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\Project;
use App\Models\ProjectBookmark;
use App\Models\ProjectVote;
use App\Models\CommunityPostVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfiniteFeedController extends Controller
{
    /**
     * Fragments of the project feed (for infinite scroll).
     * Mirrors ProjectController@index querying but returns only card HTML.
     */
    public function projectFeed(Request $request)
    {
        $tab = $request->query('tab', 'trend');
        $categorySlug = $request->query('kategori');
        $techFilter = $request->query('tech');
        $search = $request->query('q') ?? $request->query('search');

        $query = Project::with(['user', 'category'])->visibleTo(Auth::id())->search($search);

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }
        if ($techFilter) {
            $query->where('tech_stacks', 'like', "%{$techFilter}%");
        }

        switch ($tab) {
            case 'following':
                $ids = Auth::check() ? Auth::user()->followingIds() : [];
                $query->whereIn('user_id', $ids)->recent();
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
            default:
                $query->trending();
                break;
        }

        $projects = $query->paginate(12)->withQueryString();

        $userId = Auth::id();
        $userBookmarkedIds = $userId ? ProjectBookmark::where('user_id', $userId)->pluck('project_id')->toArray() : [];
        $userVotes = $userId ? ProjectVote::where('user_id', $userId)->pluck('type', 'project_id')->toArray() : [];
        $userRepostedIds = $userId ? \App\Models\ProjectRepost::where('user_id', $userId)->pluck('project_id')->toArray() : [];

        return response()
            ->view('feeds._project-items', compact('projects', 'userBookmarkedIds', 'userVotes', 'userRepostedIds'))
            ->header('X-Page', $projects->currentPage())
            ->header('X-Last-Page', $projects->lastPage());
    }

    /**
     * Fragments of the community post feed.
     */
    public function communityFeed(Request $request)
    {
        $tab = $request->query('tab', 'untukmu');
        $search = $request->query('q');
        $userId = Auth::id();

        $visibleIds = Community::visibleTo($userId)->pluck('id')->toArray();
        $joinedIds = $userId ? \App\Models\CommunityMember::where('user_id', $userId)->pluck('community_id')->toArray() : [];

        $q = CommunityPost::with(['user', 'community'])->whereIn('community_id', $visibleIds);

        if ($tab === 'diikuti') {
            $q->whereIn('community_id', $joinedIds);
        } elseif ($tab === 'populer') {
            $q->orderByDesc('is_pinned')->orderByDesc('score');
        } elseif ($tab === 'baru') {
            $q->orderByDesc('is_pinned')->orderByDesc('created_at');
        } else {
            $q->orderByDesc('is_pinned')->orderByDesc('created_at');
        }

        if ($search) {
            $q->where('content', 'like', "%{$search}%");
        }

        $posts = $q->paginate(12)->withQueryString();
        $userVotes = $userId
            ? CommunityPostVote::where('user_id', $userId)->whereIn('community_post_id', $posts->pluck('id'))->pluck('type', 'community_post_id')->toArray()
            : [];

        return response()
            ->view('feeds._community-items', compact('posts', 'userVotes'))
            ->header('X-Page', $posts->currentPage())
            ->header('X-Last-Page', $posts->lastPage());
    }

    /**
     * Fragments of a single community's post feed.
     */
    public function communityShowFeed(Request $request, Community $community)
    {
        $tab = $request->query('tab', 'terbaru');
        $search = $request->query('q');
        $userId = Auth::id();

        $posts = $community->posts()
            ->with(['user', 'comments.user'])
            ->when($tab === 'terjawab', fn ($q) => $q->where('is_answered', true))
            ->when($search, fn ($q) => $q->where('content', 'like', "%{$search}%"))
            ->orderByDesc('is_pinned')
            ->sortTab($tab)
            ->paginate(10)
            ->withQueryString();

        $userVotes = $userId
            ? CommunityPostVote::where('user_id', $userId)->whereIn('community_post_id', $posts->pluck('id'))->pluck('type', 'community_post_id')->toArray()
            : [];

        $isMember = $community->hasMember($userId);
        $isModerator = $community->isModerator($userId);

        return response()
            ->view('feeds._community-show-items', compact('posts', 'userVotes', 'isMember', 'isModerator'))
            ->header('X-Page', $posts->currentPage())
            ->header('X-Last-Page', $posts->lastPage());
    }

    /**
     * Fragments of the bookmarks (collection) feed.
     */
    public function bookmarkFeed(Request $request)
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(401);
        }

        $projectIds = ProjectBookmark::where('user_id', $userId)->pluck('project_id');
        $projects = Project::with(['user', 'category'])->whereIn('id', $projectIds)->latest()->paginate(12);
        $userBookmarkedIds = $projectIds->toArray();
        $userVotes = ProjectVote::where('user_id', $userId)->pluck('type', 'project_id')->toArray();

        return response()
            ->view('feeds._project-items', compact('projects', 'userBookmarkedIds', 'userVotes'))
            ->with('userRepostedIds', [])
            ->header('X-Page', $projects->currentPage())
            ->header('X-Last-Page', $projects->lastPage());
    }
}
