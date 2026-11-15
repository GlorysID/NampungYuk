<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    private const TABS = ['akun', 'keamanan', 'privasi', 'notifikasi', 'tampilan'];

    /**
     * Display the comprehensive account settings workspace.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadCount(['projects', 'followers', 'following', 'comments']);

        $requestedTab = (string) ($request->session()->get('settings_tab') ?? $request->query('tab', 'akun'));
        $activeTab = in_array($requestedTab, self::TABS, true) ? $requestedTab : 'akun';

        $sessions = $this->activeSessionsFor($user, $request->session()->getId());
        $blockedUsers = $user->blockedUsers()
            ->orderByDesc('user_blocks.created_at')
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar']);

        return view('settings.index', [
            'user' => $user,
            'activeTab' => $activeTab,
            'sessions' => $sessions,
            'blockedUsers' => $blockedUsers,
            'preferences' => $user->resolvedPreferences(),
        ]);
    }

    /**
     * Update basic account contact details (email).
     */
    public function updateAccount(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $hasPassword = filled($user->password);

        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if ($hasPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules, [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'current_password.required' => 'Masukkan password saat ini untuk mengonfirmasi perubahan email.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
        ]);

        $newEmail = strtolower(trim($validated['email']));
        $emailChanged = $newEmail !== strtolower($user->email);

        if ($emailChanged) {
            $user->forceFill([
                'email' => $newEmail,
                'email_verified_at' => null,
            ])->save();

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable) {
                // Email mailer may be unconfigured in local dev; keep email update intact.
            }

            return redirect()
                ->route('settings.index', ['tab' => 'akun'])
                ->with('success', 'Email berhasil diperbarui! Silakan cek inbox untuk verifikasi ulang.');
        }

        return redirect()
            ->route('settings.index', ['tab' => 'akun'])
            ->with('status', 'Tidak ada perubahan pada alamat email.');
    }

    /**
     * Resend email verification link from settings.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('settings.index', ['tab' => 'akun'])
                ->with('status', 'Alamat email kamu sudah terverifikasi.');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable) {
            return redirect()
                ->route('settings.index', ['tab' => 'akun'])
                ->with('error', 'Layanan email belum tersedia saat ini. Coba beberapa saat lagi.');
        }

        return redirect()
            ->route('settings.index', ['tab' => 'akun'])
            ->with('success', 'Tautan verifikasi email telah dikirim ulang.');
    }

    /**
     * Export user's personal archive data as a downloadable JSON file.
     */
    public function exportData(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'reputation_points' => $user->reputation_points,
                'joined_at' => $user->created_at?->toIso8601String(),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'google_connected' => filled($user->google_id),
            ],
            'preferences' => $user->resolvedPreferences(),
            'links' => $user->links()->get(['label', 'url'])->toArray(),
            'projects' => $user->projects()->get([
                'id', 'title', 'slug', 'tagline', 'type', 'status', 'visibility', 'tech_stacks', 'upvotes_count', 'created_at',
            ])->toArray(),
            'comments' => $user->comments()->get(['id', 'project_id', 'content', 'created_at'])->toArray(),
            'stats' => [
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ],
        ];

        $filename = 'nampungyuk-archive-'.$user->username.'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    /**
     * Terminate a single active session on another device.
     */
    public function destroySession(Request $request, string $sessionId): RedirectResponse
    {
        if ($sessionId === $request->session()->getId()) {
            return redirect()
                ->route('settings.index', ['tab' => 'keamanan'])
                ->with('error', 'Gunakan tombol Keluar di sidebar untuk mengakhiri sesi perangkat ini.');
        }

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('id', $sessionId)
                ->where('user_id', $request->user()->id)
                ->delete();
        }

        return redirect()
            ->route('settings.index', ['tab' => 'keamanan'])
            ->with('success', 'Sesi perangkat tersebut berhasil dikeluarkan.');
    }

    /**
     * Terminate all other active sessions for the current user.
     */
    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $deleted = 0;

        if (Schema::hasTable('sessions')) {
            $deleted = DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $message = $deleted > 0
            ? "{$deleted} sesi perangkat lain berhasil dikeluarkan."
            : 'Tidak ada sesi perangkat lain yang aktif.';

        return redirect()
            ->route('settings.index', ['tab' => 'keamanan'])
            ->with('success', $message);
    }

    /**
     * Update privacy and social safety preferences.
     */
    public function updatePrivacy(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'dm_policy' => ['required', Rule::in(['everyone', 'following_only'])],
            'allow_reposts' => ['nullable', 'boolean'],
            'discoverable' => ['nullable', 'boolean'],
            'show_read_receipts' => ['nullable', 'boolean'],
        ]);

        $prefs = $user->resolvedPreferences();
        $prefs['privacy'] = [
            'dm_policy' => $validated['dm_policy'],
            'allow_reposts' => $request->boolean('allow_reposts'),
            'discoverable' => $request->boolean('discoverable'),
            'show_read_receipts' => $request->boolean('show_read_receipts'),
        ];

        $user->update(['preferences' => $prefs]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pengaturan privasi diperbarui.']);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'privasi'])
            ->with('success', 'Pengaturan privasi dan keamanan sosial diperbarui.');
    }

    /**
     * Block a user by username.
     */
    public function blockUser(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:60'],
        ], [
            'username.required' => 'Masukkan username akun yang ingin diblokir.',
        ]);

        $cleanUsername = ltrim(trim($validated['username']), '@');
        $target = User::where('username', $cleanUsername)->first();

        if (! $target) {
            $message = "Akun @{$cleanUsername} tidak ditemukan.";

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->route('settings.index', ['tab' => 'privasi'])->withErrors(['block_username' => $message]);
        }

        if ($target->id === $user->id) {
            $message = 'Kamu tidak bisa memblokir akun sendiri.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->route('settings.index', ['tab' => 'privasi'])->withErrors(['block_username' => $message]);
        }

        $user->blockedUsers()->syncWithoutDetaching([$target->id]);
        $user->following()->detach($target->id);
        $target->following()->detach($user->id);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "@{$target->username} berhasil diblokir.",
                'user' => ['id' => $target->id, 'username' => $target->username],
            ]);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'privasi'])
            ->with('success', "@{$target->username} berhasil diblokir.");
    }

    /**
     * Unblock a previously blocked user.
     */
    public function unblockUser(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $request->user()->blockedUsers()->detach($user->id);

        $message = "Blokir untuk @{$user->username} telah dibuka.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'privasi'])
            ->with('success', $message);
    }

    /**
     * Update granular notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $prefs = $user->resolvedPreferences();
        $prefs['notifications'] = [
            'new_follower' => $request->boolean('new_follower'),
            'project_comment' => $request->boolean('project_comment'),
            'project_repost' => $request->boolean('project_repost'),
            'new_message' => $request->boolean('new_message'),
            'project_uploaded' => $request->boolean('project_uploaded'),
            'project_trending' => $request->boolean('project_trending'),
        ];

        $user->update(['preferences' => $prefs]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Preferensi notifikasi disimpan.']);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'notifikasi'])
            ->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }

    /**
     * Update media autoplay and keyboard shortcut preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'autoplay_media' => ['nullable', 'boolean'],
            'keyboard_shortcuts' => ['nullable', 'boolean'],
        ]);

        $prefs = $user->resolvedPreferences();
        $prefs['display'] = [
            'autoplay_media' => $request->boolean('autoplay_media'),
            'keyboard_shortcuts' => $request->boolean('keyboard_shortcuts'),
        ];

        $user->update(['preferences' => $prefs]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Preferensi media dan pintasan disimpan.']);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'tampilan'])
            ->with('success', 'Preferensi media dan pintasan berhasil disimpan.');
    }

    /**
     * Permanently delete the authenticated user's account.
     */
    public function destroyAccount(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (filled($user->password)) {
            $request->validate([
                'delete_password' => ['required', 'current_password'],
            ], [
                'delete_password.required' => 'Masukkan password untuk mengonfirmasi penghapusan akun.',
                'delete_password.current_password' => 'Password konfirmasi tidak sesuai.',
            ]);
        } else {
            $request->validate([
                'confirm_username' => ['required', 'string'],
            ], [
                'confirm_username.required' => 'Ketik username akunmu untuk konfirmasi.',
            ]);

            if (ltrim(trim((string) $request->input('confirm_username')), '@') !== $user->username) {
                return redirect()
                    ->route('settings.index', ['tab' => 'akun'])
                    ->withErrors(['confirm_username' => "Ketik '{$user->username}' dengan tepat untuk menghapus akun."]);
            }
        }

        // Remove locally uploaded avatar/banner assets before deleting the user row.
        foreach ([$user->getRawOriginal('avatar'), $user->getRawOriginal('banner')] as $asset) {
            if ($asset && str_contains($asset, '/storage/')) {
                $relative = ltrim(strstr($asset, '/storage/'), '/');
                $diskPath = preg_replace('#^storage/#', '', $relative);
                if ($diskPath) {
                    Storage::disk('public')->delete($diskPath);
                }
            }
        }

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Akun kamu telah dihapus secara permanen.');
    }

    /**
     * Collect active database sessions for the given user.
     *
     * @return Collection<int, object>
     */
    private function activeSessionsFor(User $user, string $currentSessionId): Collection
    {
        if (! Schema::hasTable('sessions')) {
            return collect();
        }

        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function (object $session) use ($currentSessionId): object {
                $agent = $this->parseUserAgent((string) ($session->user_agent ?? ''));

                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address ?: 'Tidak diketahui',
                    'device' => $agent['device'],
                    'browser' => $agent['browser'],
                    'platform' => $agent['platform'],
                    'is_current' => $session->id === $currentSessionId,
                    'last_active' => Carbon::createFromTimestamp((int) $session->last_activity)->diffForHumans(),
                ];
            });
    }

    /**
     * Extract readable platform, browser, and device type from a User-Agent string.
     *
     * @return array{platform: string, browser: string, device: string}
     */
    private function parseUserAgent(string $ua): array
    {
        $platform = match (true) {
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Macintosh'), str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Perangkat Lain',
        };

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Microsoft Edge',
            str_contains($ua, 'OPR/'), str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Edg/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome/') => 'Safari',
            default => 'Browser',
        };

        $device = (str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone'))
            ? 'mobile'
            : 'desktop';

        return [
            'platform' => $platform,
            'browser' => $browser,
            'device' => $device,
        ];
    }
}
