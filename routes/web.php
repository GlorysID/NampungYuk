<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/project/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/feed/latest', [ProjectController::class, 'latest'])->name('projects.latest');

Route::middleware('auth')->group(function () {
    Route::get('/unggah', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/unggah', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/project/{project}/vote', [ProjectController::class, 'vote'])->name('projects.vote');
    Route::post('/project/{project}/comment', [ProjectController::class, 'comment'])->name('projects.comment');
    Route::post('/project/{project}/bookmark', [ProjectController::class, 'bookmark'])->name('projects.bookmark');
    Route::get('/koleksi', [ProjectController::class, 'bookmarks'])->name('projects.bookmarks');

    // Social graph: follow / unfollow
    Route::post('/u/{user:username}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Notifications
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::post('/notifikasi/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

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
