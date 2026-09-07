<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
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
    public function callback(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        if (empty($clientId)) {
            return redirect()->route('login')->with('status', 'Konfigurasi Google OAuth belum disetel di .env.');
        }

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();

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
                ]);
            }

            Auth::login($user, true);

            return redirect()->intended(route('projects.index'))
                ->with('status', 'Selamat datang, '.$user->name.'! Berhasil masuk dengan Google.');
        } catch (Throwable $e) {
            Log::error('Google OAuth callback error: '.$e->getMessage());

            return redirect()->route('login')
                ->with('status', 'Gagal masuk dengan Google. Silakan coba lagi atau gunakan login username/email.');
        }
    }
}
