<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordOtpController extends Controller
{
    public function __construct(private SecurityCodes $codes) {}

    /**
     * Show the "forgot password" email form.
     */
    public function showEmailForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a login code to a registered address.
     */
    public function sendCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim((string) $validated['email']))])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email ini tidak terdaftar.'])->onlyInput('email');
        }

        $sent = $this->codes->send(
            SecurityCodes::PURPOSE_PASSWORD_RESET,
            $user->id,
            $user->email,
            'Kode masuk NampungYuk',
            ['Masukkan kode ini untuk masuk ke akunmu tanpa perlu password.'],
        );

        if ($sent === null) {
            return back()->withErrors(['email' => 'Kode gagal dikirim lewat email. Coba lagi beberapa saat lagi.'])->onlyInput('email');
        }

        $request->session()->put('password_otp.user_id', $user->id);
        $request->session()->put('password_otp.email', $user->email);

        return redirect()->route('password.code')
            ->with('status', 'Kode 6 digit dikirim ke '.$user->email.'.');
    }

    /**
     * Show the code entry form.
     */
    public function showCodeForm(Request $request): RedirectResponse|View
    {
        if (! $request->session()->get('password_otp.user_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.password-code', [
            'email' => $request->session()->get('password_otp.email'),
        ]);
    }

    /**
     * Verify the code and log the user straight in.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('password_otp.user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('password.request');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        if (! $this->codes->verify(SecurityCodes::PURPOSE_PASSWORD_RESET, $user->id, (string) $validated['code'])) {
            return back()->withErrors(['code' => 'Kode tidak valid atau sudah kedaluwarsa.'])->onlyInput('code');
        }

        $request->session()->forget(['password_otp.user_id', 'password_otp.email']);

        // Reading the code proves control of the inbox.
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('projects.index')
            ->with('success', 'Berhasil masuk. Sebaiknya perbarui passwordmu sekarang.');
    }
}
