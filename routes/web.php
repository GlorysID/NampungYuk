<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/unggah', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/unggah', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/project/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::post('/project/{project}/vote', [ProjectController::class, 'vote'])->name('projects.vote');
Route::post('/project/{project}/comment', [ProjectController::class, 'comment'])->name('projects.comment');
Route::post('/project/{project}/bookmark', [ProjectController::class, 'bookmark'])->name('projects.bookmark');
Route::get('/koleksi', [ProjectController::class, 'bookmarks'])->name('projects.bookmarks');

// User profile public route
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::redirect('/masuk', '/login');
Route::redirect('/daftar', '/register');
