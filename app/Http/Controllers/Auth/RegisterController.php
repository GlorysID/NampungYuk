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
        User::create($request->validated());

        return redirect()
            ->route('register')
            ->with('status', 'Registrasi berhasil! Akun Anda telah dibuat.');
    }
}
