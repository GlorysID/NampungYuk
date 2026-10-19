<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return redirect()->route('projects.index');
        }

        $email = $request->query('email') ?? $request->user()?->email ?? session('unverified_email');

        return view('auth.verify-email', compact('email'));
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, int|string $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Tautan verifikasi tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Tautan verifikasi tidak cocok. Silakan coba lagi.']);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if (! Auth::check()) {
            Auth::login($user);
        }

        return redirect()->route('projects.index')
            ->with('status', 'Alamat email berhasil diverifikasi! Selamat datang di NampungYuk, '.$user->name.'.');
    }

    /**
     * Send a new email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['nullable', 'email'],
        ]);

        $email = $request->input('email') ?? $request->user()?->email ?? session('unverified_email');

        if (! $email) {
            return back()->withErrors(['email' => 'Silakan cantumkan alamat email untuk mengirim ulang verifikasi.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->hasVerifiedEmail()) {
                return redirect()->route('login')
                    ->with('status', 'Alamat email ini sudah terverifikasi. Silakan masuk.');
            }

            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', 'Tautan verifikasi baru telah dikirim ke '.$email.'. Silakan periksa kotak masuk atau folder spam kamu.');
    }
}
