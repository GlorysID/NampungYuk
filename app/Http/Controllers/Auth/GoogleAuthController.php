<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseIdTokenVerifier;
use Illuminate\Http\JsonResponse;
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

            $user = $this->findOrCreateGoogleUser(
                googleId: (string) $googleUser->getId(),
                email: (string) $googleUser->getEmail(),
                name: $googleUser->getName(),
                avatar: $googleUser->getAvatar(),
            );

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('projects.index'))
                ->with('status', 'Selamat datang, '.$user->name.'! Berhasil masuk dengan Google.');
        } catch (Throwable $e) {
            Log::error('Google OAuth callback error ('.get_class($e).'): '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());

            return redirect()->route('login')
                ->with('status', 'Gagal masuk dengan Google. Silakan coba lagi atau gunakan login username/email.');
        }
    }

    /**
     * Authenticate or register a user using a verified Firebase ID token.
     */
    public function firebaseLogin(Request $request, FirebaseIdTokenVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        if (empty(config('services.firebase.project_id'))) {
            return response()->json([
                'message' => 'Firebase Auth belum dikonfigurasi di server.',
            ], 503);
        }

        try {
            $claims = $verifier->verify($validated['id_token']);

            $user = $this->findOrCreateGoogleUser(
                googleId: $claims['google_id'],
                email: $claims['email'],
                name: $claims['name'],
                avatar: $claims['avatar'],
            );

            Auth::login($user, true);
            $request->session()->regenerate();

            $welcomeMessage = 'Selamat datang, '.$user->name.'! Berhasil masuk dengan Google.';
            $request->session()->flash('status', $welcomeMessage);

            return response()->json([
                'redirect' => $request->session()->pull('url.intended', route('projects.index')),
                'message' => $welcomeMessage,
            ]);
        } catch (Throwable $e) {
            Log::warning('Firebase Google login gagal: '.$e->getMessage(), [
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => 'Token login Google dari Firebase tidak valid atau kedaluwarsa. Silakan coba lagi.',
            ], 401);
        }
    }

    /**
     * Find an existing user by Google ID or email, or provision a new verified account.
     */
    private function findOrCreateGoogleUser(string $googleId, string $email, ?string $name = null, ?string $avatar = null): User
    {
        $normalizedEmail = strtolower(trim($email));

        $user = User::where('google_id', $googleId)
            ->orWhereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if ($user) {
            if (! $user->google_id) {
                $user->google_id = $googleId;
            }
            if (! $user->avatar && ! empty($avatar)) {
                $user->avatar = $avatar;
            }
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();

            return $user;
        }

        $baseUsername = Str::slug(explode('@', $normalizedEmail)[0], '_');
        if (empty($baseUsername)) {
            $baseUsername = 'dev_'.Str::random(5);
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.'_'.$counter;
            $counter++;
        }

        return User::create([
            'name' => ! empty($name) ? $name : $username,
            'username' => $username,
            'email' => $normalizedEmail,
            'google_id' => $googleId,
            'avatar' => $avatar,
            'password' => null,
            'email_verified_at' => now(),
        ]);
    }
}
