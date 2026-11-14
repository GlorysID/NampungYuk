<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->with(
                'status',
                'Google Login belum aktif. Silakan isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env terlebih dahulu.'
            );
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        if (empty($clientId)) {
            return redirect()->route('login')->with('status', 'Konfigurasi Google OAuth belum disetel di .env.');
        }

        if ($request->filled('error')) {
            $oauthError = (string) $request->query('error');
            $oauthDesc = (string) $request->query('error_description', '');

            Log::warning('Google OAuth returned error on callback', [
                'error' => $oauthError,
                'error_description' => $oauthDesc,
            ]);

            $message = $oauthError === 'access_denied'
                ? 'Login Google dibatalkan atau akses ditolak. Jika aplikasi masih mode Testing di Google Cloud Console, pastikan email Google kamu terdaftar di Test Users.'
                : 'Gagal masuk dengan Google ('.$oauthError.'). Silakan coba lagi atau gunakan login username/email.';

            return redirect()->route('login')->with('status', $message);
        }

        if (! $request->filled('code')) {
            Log::warning('Google OAuth callback dipanggil tanpa parameter code.', [
                'host' => $request->getHost(),
            ]);

            return redirect()->route('login')
                ->with('status', 'Sesi otorisasi Google tidak lengkap. Silakan klik tombol Lanjutkan dengan Google lagi.');
        }

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (InvalidStateException) {
                // Fallback ke stateless jika session state hilang saat redirect dari Google (misal: mismatch host 127.0.0.1 vs localhost atau cookie cross-site diblokir browser)
                $googleUser = Socialite::driver('google')->stateless()->user();
            }

            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Update google_id and avatar if not present
                if (! $user->google_id) {
                    $user->google_id = $googleUser->getId();
                }
                if (! $user->avatar && $googleUser->getAvatar()) {
                    $user->avatar = $googleUser->getAvatar();
                }
                // Google accounts are pre-verified, so verify the email if not already verified
                if (! $user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();
            } else {
                // Generate a unique username
                $baseUsername = Str::slug(explode('@', $googleUser->getEmail())[0], '_');
                if (empty($baseUsername)) {
                    $baseUsername = 'dev_'.Str::random(5);
                }

                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername.'_'.$counter;
                    $counter++;
                }

                $user = User::create([
                    'name' => $googleUser->getName() ?: $username,
                    'username' => $username,
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => null,
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true);

            return redirect()->intended(route('projects.index'))
                ->with('status', 'Selamat datang, '.$user->name.'! Berhasil masuk dengan Google.');
        } catch (Throwable $e) {
            Log::error('Google OAuth callback error ('.get_class($e).'): '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());

            return redirect()->route('login')
                ->with('status', 'Gagal masuk dengan Google. Silakan coba lagi atau gunakan login username/email.');
        }
    }
}
