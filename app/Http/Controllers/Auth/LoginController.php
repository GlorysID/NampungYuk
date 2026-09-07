<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $isEmail = filter_var($request->login, FILTER_VALIDATE_EMAIL) !== false;

        $credentials = [
            'password' => $request->password,
            $isEmail ? 'email' : 'username' => $request->login,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->hitRateLimiter();

            return back()
                ->withErrors(['login' => 'Email/Username atau password salah.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(url('/'))
            ->with('status', 'Selamat datang kembali, '.Auth::user()->name.'!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(url('/'))->with('status', 'Anda telah keluar.');
    }
}
