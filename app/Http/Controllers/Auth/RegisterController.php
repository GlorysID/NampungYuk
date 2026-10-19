<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request and store a new user.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('login')
            ->with('status', 'Registrasi berhasil! Tautan verifikasi telah dikirim ke email '.$user->email.'. Silakan periksa kotak masuk atau spam sebelum masuk.')
            ->with('unverified_email', $user->email);
    }
}
