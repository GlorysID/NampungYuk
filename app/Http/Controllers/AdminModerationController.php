<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModerationController extends Controller
{
    /**
     * Display moderation dashboard with reports.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab');
        if (! $tab) {
            $tab = $request->routeIs('admin.users.*') ? 'all_users' : 'projects';
        }

        $pendingCount = Report::pending()->count();
        $pendingProjectReportsCount = Report::pending()->where('reportable_type', Project::class)->count();
        $pendingUserReportsCount = Report::pending()->where('reportable_type', User::class)->count();
        $bannedUsersCount = User::where('is_banned', true)->count();
        $warnedUsersCount = User::whereNotNull('warning_notes')->count();
        $allUsersCount = User::count();

        $projectReports = Report::with(['reporter', 'reportable.user'])
            ->where('reportable_type', Project::class)
            ->latest()
            ->paginate(15, ['*'], 'p_page')
            ->withQueryString();

        $userReports = Report::with(['reporter', 'reportable'])
            ->where('reportable_type', User::class)
            ->latest()
            ->paginate(15, ['*'], 'u_page')
            ->withQueryString();

        $bannedUsers = User::where('is_banned', true)
            ->latest('banned_at')
            ->paginate(15, ['*'], 'b_page')
            ->withQueryString();

        $searchQuery = trim((string) $request->query('q'));
        $allUsersQuery = User::withCount('projects');
        if ($searchQuery !== '') {
            $allUsersQuery->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('username', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }
        $allUsers = $allUsersQuery->latest()->paginate(15, ['*'], 'users_page')->withQueryString();

        return view('admin.reports', compact(
            'tab',
            'pendingCount',
            'pendingProjectReportsCount',
            'pendingUserReportsCount',
            'bannedUsersCount',
            'warnedUsersCount',
            'allUsersCount',
            'projectReports',
            'userReports',
            'bannedUsers',
            'allUsers',
            'searchQuery'
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

        return back()->with('status', "Peringatan resmi berhasil dipasang pada proyek '{$project->title}'.");
    }

    /**
     * Delete a project as Admin.
     */
    public function destroyProject(Project $project, Request $request): RedirectResponse
    {
        $title = $project->title;

        // Auto resolve pending reports for this project
        Report::where('reportable_type', Project::class)
            ->where('reportable_id', $project->id)
            ->update([
                'status' => 'resolved',
                'admin_notes' => 'Proyek telah dihapus oleh Admin.',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        $project->delete();

        return redirect()->route('projects.index')->with('status', "Proyek '{$title}' berhasil dihapus secara permanen oleh Admin.");
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

        // Clean up user's projects and relations
        $user->projects()->delete();
        $user->comments()->delete();
        $user->communityPosts()->delete();
        $user->delete();

        return redirect()->route('projects.index')->with('status', "Akun @{$username} dan seluruh datanya telah berhasil dihapus total oleh Admin.");
    }
}
