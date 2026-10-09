<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/project/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/feed/latest', [ProjectController::class, 'latest'])->name('projects.latest');

// Communities (public view)
Route::get('/komunitas', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/komunitas/{slug}', [CommunityController::class, 'show'])->name('communities.show');

// Chat / Obrolan (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/obrolan', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/obrolan/{user:username}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/obrolan/{user:username}/kirim', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
});

// Infinite-scroll feed fragments (HTML partials)
Route::get('/feed-fragment/projects', [\App\Http\Controllers\InfiniteFeedController::class, 'projectFeed'])->name('feeds.projects');
Route::get('/feed-fragment/communities', [\App\Http\Controllers\InfiniteFeedController::class, 'communityFeed'])->name('feeds.communities');
Route::get('/feed-fragment/community/{community:slug}', [\App\Http\Controllers\InfiniteFeedController::class, 'communityShowFeed'])->name('feeds.community');
Route::get('/feed-fragment/bookmarks', [\App\Http\Controllers\InfiniteFeedController::class, 'bookmarkFeed'])->name('feeds.bookmarks');

Route::middleware('auth')->group(function () {
    Route::get('/unggah', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/unggah', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/project/{project}/vote', [ProjectController::class, 'vote'])->name('projects.vote');
    Route::post('/project/{project}/comment', [ProjectController::class, 'comment'])->name('projects.comment');
    Route::post('/project/{project}/bookmark', [ProjectController::class, 'bookmark'])->name('projects.bookmark');
    Route::get('/koleksi', [ProjectController::class, 'bookmarks'])->name('projects.bookmarks');

    // Social graph: follow / unfollow
    Route::post('/u/{user:username}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Profile settings
    Route::put('/pengaturan/profil', [ProfileController::class, 'update'])->name('profile.update');

    // Project repost & pin
    Route::post('/project/{project}/repost', [ProjectController::class, 'repost'])->name('projects.repost');
    Route::post('/project/{project}/pin', [ProjectController::class, 'togglePin'])->name('projects.pin');

    // Notifications
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::post('/notifikasi/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Communities (member actions)
    Route::post('/komunitas', [CommunityController::class, 'store'])->name('communities.store');
    Route::post('/komunitas/{community:slug}/join', [CommunityController::class, 'toggleJoin'])->name('communities.join');
    Route::post('/komunitas/{community:slug}/post', [CommunityPostController::class, 'store'])->name('communities.post');
    Route::post('/komunitas/{community:slug}/moderator/{user}', [CommunityController::class, 'toggleModerator'])->name('communities.moderator');
    Route::post('/community-post/{post}/vote', [CommunityPostController::class, 'vote'])->name('communities.post.vote');
    Route::post('/community-post/{post}/comment', [CommunityPostController::class, 'comment'])->name('communities.post.comment');

    // Community moderation
    Route::post('/community-post/{post}/pin', [CommunityPostController::class, 'togglePin'])->name('communities.post.pin');
    Route::post('/community-post/{post}/answered', [CommunityPostController::class, 'markAnswered'])->name('communities.post.answered');
    Route::delete('/community-post/{post}', [CommunityPostController::class, 'destroy'])->name('communities.post.destroy');
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
