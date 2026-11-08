<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Project;
use App\Models\ProjectBookmark;
use App\Models\ProjectRepost;
use App\Models\ProjectVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Global search page (X-style): tabbed results for projects, people, communities.
     */
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));
        $tabs = ['populer', 'terbaru', 'orang', 'karya', 'komunitas'];
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : 'populer';
        $viewerId = Auth::id();

        $projects = collect();
        $people = collect();
        $communities = collect();

        if ($term !== '') {
            if ($tab === 'orang') {
                $people = User::query()
                    ->search($term)
                    ->whereNull('ban_reason')
                    ->orderByDesc('reputation_points')
                    ->paginate(20)
                    ->withQueryString();
            } elseif ($tab === 'komunitas') {
                $communities = Community::query()
                    ->visibleTo($viewerId)
                    ->search($term)
                    ->orderByDesc('members_count')
                    ->paginate(20)
                    ->withQueryString();
            } elseif ($tab === 'karya' || $tab === 'terbaru') {
                $query = Project::with(['user', 'category'])
                    ->visibleTo($viewerId)
                    ->search($term);

                $projects = ($tab === 'terbaru' ? $query->recent() : $query->trending())
                    ->paginate(12)
                    ->withQueryString();
            } else {
                $projects = Project::with(['user', 'category'])
                    ->visibleTo($viewerId)
                    ->search($term)
                    ->trending()
                    ->take(6)
                    ->get();

                $people = User::query()
                    ->search($term)
                    ->whereNull('ban_reason')
                    ->orderByDesc('reputation_points')
                    ->take(3)
                    ->get();

                $communities = Community::query()
                    ->visibleTo($viewerId)
                    ->search($term)
                    ->orderByDesc('members_count')
                    ->take(3)
                    ->get();
            }
        }

        $userVotes = $viewerId
            ? ProjectVote::where('user_id', $viewerId)->pluck('type', 'project_id')->toArray()
            : [];
        $userBookmarkedIds = $viewerId
            ? ProjectBookmark::where('user_id', $viewerId)->pluck('project_id')->toArray()
            : [];
        $userRepostedIds = $viewerId
            ? ProjectRepost::where('user_id', $viewerId)->pluck('project_id')->toArray()
            : [];

        return view('search.index', compact(
            'term',
            'tab',
            'projects',
            'people',
            'communities',
            'userVotes',
            'userBookmarkedIds',
            'userRepostedIds',
        ));
    }
}
