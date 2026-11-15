<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\VerifyCurrentPasswordRequest;
use App\Http\Requests\VerifyPasswordCodeRequest;
use App\Services\SecurityCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    /**
     * How long a successful identity check (current password or emailed code) stays valid.
     */
    private const VERIFICATION_WINDOW_MINUTES = 15;

    public function __construct(private SecurityCodes $codes) {}

    /**
     * Show the staged password form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $hasPassword = filled($user->password);

        return view('settings.password.edit', [
            'user' => $user,
            'hasPassword' => $hasPassword,
            'needsCurrentPassword' => $hasPassword && ! $this->isVerified($request),
        ]);
    }

    /**
     * Verify the current password to unlock the new-password step.
     */
    public function verifyCurrent(VerifyCurrentPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check((string) $request->validated('current_password'), (string) $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
        }

        $request->session()->put('password_change.verified_at', now()->timestamp);

        return redirect()->route('settings.password.edit');
    }

    /**
     * Persist the new password once identity has been verified.
     */
    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $hasPassword = filled($user->password);

        if ($hasPassword && ! $this->isVerified($request)) {
            return redirect()->route('settings.password.edit')
                ->with('error', 'Konfirmasi password saat ini dulu sebelum mengganti password.');
        }

        $user->update(['password' => $request->validated('password')]);
        $request->session()->forget('password_change.verified_at');

        return redirect()->route('settings.index', ['tab' => 'keamanan'])
            ->with('success', $hasPassword ? 'Password berhasil diperbarui.' : 'Password berhasil dibuat.');
    }

    /**
     * Email a code for users who forgot their current password.
     */
    public function sendCode(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->email)) {
            return back()->with('error', 'Akunmu belum punya alamat email. Tambahkan email dulu sebelum mengganti password.');
        }

        $sent = $this->codes->send(
            SecurityCodes::PURPOSE_PASSWORD_RESET,
            $user->id,
            $user->email,
            'Kode ubah password',
            ['Masukkan kode ini di halaman keamanan untuk mengganti password akunmu.'],
        );

        if ($sent === null) {
            return back()->with('error', 'Kode gagal dikirim lewat email. Coba lagi beberapa saat lagi.');
        }

        return redirect()->route('settings.password.forgot.code')
            ->with('status', 'Kode 6 digit dikirim ke '.$user->email.'.');
    }

    /**
     * Show the code form for the "forgot current password" flow.
     */
    public function showCodeForm(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($this->codes->isEmpty(SecurityCodes::PURPOSE_PASSWORD_RESET, $user->id)) {
            return redirect()->route('settings.password.edit')
                ->with('error', 'Minta kode verifikasi dulu sebelum melanjutkan.');
        }

        return view('settings.password.forgot-code', [
            'user' => $user,
            'email' => $user->email,
        ]);
    }

    /**
     * Verify the emailed code and unlock the new-password step.
     */
    public function verifyCode(VerifyPasswordCodeRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->codes->verify(SecurityCodes::PURPOSE_PASSWORD_RESET, $user->id, (string) $request->validated('code'))) {
            return back()->withErrors(['code' => 'Kode tidak valid atau sudah kedaluwarsa.'])->onlyInput('code');
        }

        $request->session()->put('password_change.verified_at', now()->timestamp);

        return redirect()->route('settings.password.edit')
            ->with('status', 'Identitas terverifikasi. Tentukan password baru.');
    }

    /**
     * Whether the current session passed a fresh identity check.
     */
    private function isVerified(Request $request): bool
    {
        $verifiedAt = $request->session()->get('password_change.verified_at');

        return is_numeric($verifiedAt)
            && (now()->timestamp - (int) $verifiedAt) <= self::VERIFICATION_WINDOW_MINUTES * 60;
    }
}
