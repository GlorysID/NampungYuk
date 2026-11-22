<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CommunityPost;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ModerationWarningNotification;
use App\Services\MediaOptimizer;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModerationController extends Controller
{
    /**
     * Display moderation dashboard with structured sections and lazy-loaded active dataset.
     */
    public function index(Request $request): View
    {
        $rawTab = (string) $request->query('tab', '');

        if ($rawTab === '' && $request->routeIs('admin.users.*')) {
            $section = 'users';
            $tab = 'all_users';
        } else {
            [$section, $tab] = match ($rawTab) {
                'all_users', 'banned' => ['users', 'all_users'],
                'flagged' => ['flagged', 'flagged'],
                default => ['reports', 'reports'],
            };
        }

        $defaultType = match ($rawTab) {
            'projects' => 'content',
            'users' => 'user',
            default => 'all',
        };
        $reportType = (string) $request->query('type', $defaultType);
        if (! in_array($reportType, ['all', 'content', 'user'], true)) {
            $reportType = 'all';
        }

        $reportStatus = (string) $request->query('status', 'pending');
        if (! in_array($reportStatus, ['pending', 'history', 'all'], true)) {
            $reportStatus = 'pending';
        }

        $defaultFilter = $rawTab === 'banned' ? 'banned' : 'all';
        $userFilter = (string) $request->query('filter', $defaultFilter);
        if (! in_array($userFilter, ['all', 'active', 'warned', 'banned', 'admin'], true)) {
            $userFilter = 'all';
        }

        $searchQuery = trim((string) $request->query('q', ''));

        $pendingCount = Report::pending()->count();
        $pendingContentReportsCount = Report::pending()
            ->whereIn('reportable_type', [Project::class, CommunityPost::class])
            ->count();
        $pendingUserReportsCount = Report::pending()
            ->where('reportable_type', User::class)
            ->count();
        $historyReportsCount = Report::whereIn('status', ['resolved', 'dismissed'])->count();

        $flaggedProjectsCount = Project::where('visibility', '!=', 'draft')
            ->where(function ($query): void {
                $query->where('is_flagged', true)->orWhereNotNull('warning_notes');
            })
            ->count();

        $warnedUsersCount = User::whereNotNull('warning_notes')->count();
        $bannedUsersCount = User::where('is_banned', true)->count();
        $allUsersCount = User::count();
        $activeUsersCount = User::where('is_banned', false)->whereNull('warning_notes')->count();
        $adminUsersCount = User::where('is_admin', true)->count();

        $reports = null;
        $allUsers = null;
        $flaggedProjects = null;

        if ($section === 'reports') {
            $reportsQuery = Report::with([
                'reporter',
                'resolver',
                'reportable' => function (MorphTo $morphTo): void {
                    $morphTo->morphWith([
                        Project::class => ['user', 'category'],
                        CommunityPost::class => ['user', 'community'],
                    ]);
                },
            ]);

            if ($reportType === 'content') {
                $reportsQuery->whereIn('reportable_type', [Project::class, CommunityPost::class]);
            } elseif ($reportType === 'user') {
                $reportsQuery->where('reportable_type', User::class);
            }

            if ($reportStatus === 'pending') {
                $reportsQuery->where('status', 'pending')->latest();
            } elseif ($reportStatus === 'history') {
                $reportsQuery->whereIn('status', ['resolved', 'dismissed'])->latest('resolved_at')->latest();
            } else {
                $reportsQuery->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest();
            }

            $reports = $reportsQuery->paginate(15, ['*'], 'r_page')->withQueryString();
        } elseif ($section === 'users') {
            $allUsersQuery = User::withCount('projects');

            if ($searchQuery !== '') {
                $allUsersQuery->where(function ($query) use ($searchQuery): void {
                    $query->where('name', 'like', "%{$searchQuery}%")
                        ->orWhere('username', 'like', "%{$searchQuery}%")
                        ->orWhere('email', 'like', "%{$searchQuery}%");
                });
            }

            if ($userFilter === 'active') {
                $allUsersQuery->where('is_banned', false)->whereNull('warning_notes')->latest();
            } elseif ($userFilter === 'warned') {
                $allUsersQuery->whereNotNull('warning_notes')->latest('warned_at')->latest();
            } elseif ($userFilter === 'banned') {
                $allUsersQuery->where('is_banned', true)->latest('banned_at')->latest();
            } elseif ($userFilter === 'admin') {
                $allUsersQuery->where('is_admin', true)->latest();
            } else {
                $allUsersQuery->latest();
            }

            $allUsers = $allUsersQuery->paginate(15, ['*'], 'users_page')->withQueryString();
        } elseif ($section === 'flagged') {
            $flaggedProjects = Project::with(['user', 'category'])
                ->where('visibility', '!=', 'draft')
                ->where(function ($query): void {
                    $query->where('is_flagged', true)->orWhereNotNull('warning_notes');
                })
                ->latest('updated_at')
                ->paginate(15, ['*'], 'f_page')
                ->withQueryString();
        }

        return view('admin.reports', compact(
            'tab',
            'section',
            'reportType',
            'reportStatus',
            'userFilter',
            'searchQuery',
            'pendingCount',
            'pendingContentReportsCount',
            'pendingUserReportsCount',
            'historyReportsCount',
            'flaggedProjectsCount',
            'warnedUsersCount',
            'bannedUsersCount',
            'allUsersCount',
            'activeUsersCount',
            'adminUsersCount',
            'reports',
            'allUsers',
            'flaggedProjects'
        ));
    }

    /**
     * Resolve a report.
     */
    public function resolveReport(Report $report, Request $request): RedirectResponse
    {
        $report->update([
            'status' => 'resolved',
            'admin_notes' => $request->input('admin_notes', 'Tindakan telah diambil oleh Admin.'),
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('status', 'Laporan berhasil ditandai sebagai Selesai.');
    }

    /**
     * Dismiss / ignore a report.
     */
    public function dismissReport(Report $report, Request $request): RedirectResponse
    {
        $report->update([
            'status' => 'dismissed',
            'admin_notes' => $request->input('admin_notes', 'Laporan diabaikan (tidak ditemukan pelanggaran).'),
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('status', 'Laporan berhasil diabaikan.');
    }

    /**
     * Give warning notice or remove warning on a project.
     */
    public function warnProject(Project $project, Request $request): RedirectResponse
    {
        $request->validate([
            'warning_notes' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'in:set,remove'],
        ]);

        if ($request->input('action') === 'remove') {
            $project->update([
                'warning_notes' => null,
                'is_flagged' => false,
            ]);

            return back()->with('status', "Peringatan pada proyek '{$project->title}' berhasil dicabut.");
        }

        $warning = $request->input('warning_notes') ?: 'Proyek ini sedang dalam peninjauan karena berpotensi melanggar pedoman komunitas NampungYuk.';

        $project->update([
            'warning_notes' => $warning,
            'is_flagged' => true,
        ]);

        // Auto resolve pending reports for this project
        Report::where('reportable_type', Project::class)
            ->where('reportable_id', $project->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'admin_notes' => 'Peringatan telah diberikan kepada proyek.',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        $project->loadMissing('user');
        if ($project->user) {
            $project->user->notify(new ModerationWarningNotification(
                message: "Karya \"{$project->title}\" mendapatkan peringatan moderasi dari Tim NampungYuk.",
                notes: $warning,
                url: route('projects.show', $project->slug),
                actor: $request->user(),
                scope: 'project',
            ));
        }

        return back()->with('status', "Peringatan resmi berhasil dipasang pada proyek '{$project->title}'.");
    }

    /**
     * Delete a project as Admin.
     */
    public function destroyProject(Project $project, Request $request): RedirectResponse
    {
        $title = $project->title;
        $categoryId = $project->category_id;

        // Auto resolve pending reports for this project
        Report::where('reportable_type', Project::class)
            ->where('reportable_id', $project->id)
            ->update([
                'status' => 'resolved',
                'admin_notes' => 'Proyek telah dihapus oleh Admin.',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        app(MediaOptimizer::class)->deleteProjectMedia($project);

        $project->delete();

        if ($categoryId) {
            Category::where('id', $categoryId)->where('projects_count', '>', 0)->decrement('projects_count');
        }

        $message = "Konten Berhasil Dihapus. Proyek '{$title}' berhasil dihapus secara permanen.";

        if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/admin')) {
            return back()->with('status', $message);
        }

        return redirect()->route('projects.index')->with('status', $message);
    }

    /**
     * Delete a community post as Admin.
     */
    public function destroyCommunityPost(CommunityPost $post, Request $request): RedirectResponse
    {
        $community = $post->community;

        // Auto resolve pending reports for this post
        Report::where('reportable_type', CommunityPost::class)
            ->where('reportable_id', $post->id)
            ->update([
                'status' => 'resolved',
                'admin_notes' => 'Postingan komunitas telah dihapus oleh Admin.',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        foreach ($post->images ?? [] as $img) {
            app(MediaOptimizer::class)->deleteStoredFile(is_string($img) ? $img : null);
        }

        $post->delete();

        if ($community && $community->posts_count > 0) {
            $community->decrement('posts_count');
        }

        return back()->with('status', 'Postingan komunitas berhasil dihapus oleh Admin.');
    }

    /**
     * Give warning notice or remove warning on a user account.
     */
    public function warnUser(User $user, Request $request): RedirectResponse
    {
        $request->validate([
            'warning_notes' => ['nullable', 'string', 'max:500'],
            'action' => ['nullable', 'in:set,remove'],
        ]);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat memberi peringatan ke akun Anda sendiri.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Anda tidak dapat memberi peringatan ke sesama Administrator.');
        }

        if ($request->input('action') === 'remove') {
            $user->update([
                'warning_notes' => null,
                'warned_at' => null,
            ]);

            return back()->with('status', "Peringatan pada akun @{$user->username} berhasil dicabut.");
        }

        $warning = $request->input('warning_notes') ?: 'Akun Anda sedang dalam peninjauan karena terindikasi melanggar pedoman komunitas NampungYuk.';

        $user->update([
            'warning_notes' => $warning,
            'warned_at' => now(),
        ]);

        // Auto resolve pending reports for this user
        Report::where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'admin_notes' => "Peringatan resmi telah diberikan kepada pengguna: {$warning}",
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        $user->notify(new ModerationWarningNotification(
            message: 'Akun Anda mendapatkan peringatan resmi dari Tim Moderasi NampungYuk.',
            notes: $warning,
            url: route('profile.show', $user->username),
            actor: $request->user(),
            scope: 'account',
        ));

        return back()->with('status', "Peringatan resmi berhasil dikirimkan ke akun @{$user->username}.");
    }

    /**
     * Ban a user.
     */
    public function banUser(User $user, Request $request): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat memblokir akun Anda sendiri.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Anda tidak dapat memblokir sesama Administrator.');
        }

        $reason = $request->input('reason') ?: ($request->input('ban_reason') ?: 'Pelanggaran pedoman komunitas NampungYuk.');

        $user->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'banned_at' => now(),
        ]);

        // Auto resolve pending reports for this user
        Report::where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'admin_notes' => "Akun telah dinonaktifkan (ban). Alasan: {$reason}",
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        return back()->with('status', "Akun @{$user->username} berhasil dibekukan (Banned).");
    }

    /**
     * Unban a user.
     */
    public function unbanUser(User $user): RedirectResponse
    {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null,
        ]);

        return back()->with('status', "Blokir akun @{$user->username} berhasil dicabut. Akun kembali aktif.");
    }

    /**
     * Delete user account completely.
     */
    public function destroyUser(User $user, Request $request): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'Anda tidak dapat menghapus sesama Administrator.');
        }

        $username = $user->username;

        // Auto resolve pending reports targeting this user
        Report::where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'admin_notes' => 'Akun pengguna telah dihapus total oleh Admin.',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        // Clean up user's projects and relations
        foreach ($user->projects as $p) {
            if ($p->category_id) {
                Category::where('id', $p->category_id)->where('projects_count', '>', 0)->decrement('projects_count');
            }
        }
        $user->projects()->delete();
        $user->comments()->delete();
        foreach ($user->communityPosts as $cp) {
            if ($cp->community && $cp->community->posts_count > 0) {
                $cp->community->decrement('posts_count');
            }
        }
        $user->communityPosts()->delete();
        $media = app(MediaOptimizer::class);
        $media->deleteStoredFile($user->getRawOriginal('avatar'));
        $media->deleteStoredFile($user->getRawOriginal('banner'));
        $user->delete();

        $message = "Akun @{$username} dan seluruh datanya telah berhasil dihapus total oleh Admin.";
        $referer = (string) $request->headers->get('referer', '');

        if ($referer !== '' && str_contains($referer, '/admin')) {
            return back()->with('status', $message);
        }

        return redirect()->route('projects.index')->with('status', $message);
    }
}
