<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\VerifyEmailChangeCodeRequest;
use App\Models\User;
use App\Services\SecurityCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailChangeController extends Controller
{
    public function __construct(private readonly SecurityCodes $codes) {}

    /**
     * Show the form for entering a new email address, starting the flow clean.
     */
    public function edit(Request $request): View
    {
        $request->session()->forget('email_change.pending');

        /** @var User $user */
        $user = $request->user();

        return view('settings.email.edit', [
            'user' => $user,
            'hasPassword' => filled($user->password),
        ]);
    }

    /**
     * Send a 6-digit code to the NEW address. The current email stays untouched.
     */
    public function sendCode(ChangeEmailRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $newEmail = strtolower(trim((string) $request->validated()['email']));

        $request->session()->put('email_change.pending', $newEmail);

        $sent = $this->codes->send(
            SecurityCodes::PURPOSE_EMAIL_CHANGE,
            $user->id,
            $newEmail,
            'Kode verifikasi email baru',
            ['Masukkan kode ini untuk memindahkan email akun NampungYuk kamu ke alamat ini.'],
        );

        if ($sent === null) {
            return back()->with('error', 'Kode gagal dikirim lewat email. Coba lagi beberapa saat lagi.');
        }

        return redirect()
            ->route('settings.email.code')
            ->with('status', "Kode verifikasi dikirim ke {$newEmail}.");
    }

    /**
     * Show the code entry form, or bounce back when there is no live flow.
     */
    public function showCodeForm(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $pending = $request->session()->get('email_change.pending');

        if (! is_string($pending) || $pending === '' || $this->codes->isEmpty(SecurityCodes::PURPOSE_EMAIL_CHANGE, $user->id)) {
            return redirect()
                ->route('settings.email.edit')
                ->with('error', 'Mulai lagi dari awal: kirim kode verifikasi ke alamat email baru.');
        }

        return view('settings.email.code', [
            'user' => $user,
            'pendingEmail' => $pending,
        ]);
    }

    /**
     * Verify the code and move the account to the pending address.
     */
    public function verifyCode(VerifyEmailChangeCodeRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $pending = $request->session()->get('email_change.pending');

        if (! is_string($pending) || $pending === '') {
            return redirect()
                ->route('settings.email.edit')
                ->with('error', 'Sesi ganti email tidak ditemukan. Mulai lagi dari awal.');
        }

        if (! $this->codes->verify(SecurityCodes::PURPOSE_EMAIL_CHANGE, $user->id, (string) $request->validated()['code'])) {
            return back()
                ->withErrors(['code' => 'Kode tidak valid atau sudah kedaluwarsa.'])
                ->onlyInput('code');
        }

        // Race condition: someone else may have claimed the address since it was sent.
        if (User::whereRaw('LOWER(email) = ?', [$pending])->whereKeyNot($user->id)->exists()) {
            $request->session()->forget('email_change.pending');

            return redirect()
                ->route('settings.email.edit')
                ->withErrors(['email' => 'Alamat email ini sudah digunakan oleh akun lain. Coba alamat lain.']);
        }

        $user->forceFill([
            'email' => $pending,
            'email_verified_at' => now(),
        ])->save();

        $request->session()->forget('email_change.pending');

        return redirect()
            ->route('settings.index', ['tab' => 'keamanan'])
            ->with('success', 'Alamat email berhasil diubah.');
    }

    /**
     * Resend a fresh code to the pending address.
     */
    public function resend(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $pending = $request->session()->get('email_change.pending');

        if (! is_string($pending) || $pending === '') {
            return redirect()
                ->route('settings.email.edit')
                ->with('error', 'Sesi ganti email tidak ditemukan. Mulai lagi dari awal.');
        }

        $sent = $this->codes->send(
            SecurityCodes::PURPOSE_EMAIL_CHANGE,
            $user->id,
            $pending,
            'Kode verifikasi email baru',
            ['Masukkan kode ini untuk memindahkan email akun NampungYuk kamu ke alamat ini.'],
        );

        if ($sent === null) {
            return back()->with('error', 'Kode gagal dikirim lewat email. Coba lagi beberapa saat lagi.');
        }

        return redirect()
            ->route('settings.email.code')
            ->with('status', "Kode verifikasi baru dikirim ke {$pending}.");
    }
}
