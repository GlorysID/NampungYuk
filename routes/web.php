<?php

use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\InfiniteFeedController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/feed/latest', [ProjectController::class, 'latest'])->name('projects.latest');

// Chat / Obrolan (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/obrolan', [ChatController::class, 'index'])->name('chat.index');
    Route::delete('/obrolan/pesan/{message}', [ChatController::class, 'destroyMessage'])->name('chat.message.destroy');
    Route::patch('/obrolan/pesan/{message}', [ChatController::class, 'editMessage'])->name('chat.message.edit');
    Route::get('/obrolan/{user:username}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/obrolan/{user:username}/kirim', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/obrolan/{user:username}/mengetik', [ChatController::class, 'typing'])->name('chat.typing');
    Route::get('/obrolan/{user:username}/lama', [ChatController::class, 'older'])->name('chat.older');
    Route::post('/obrolan/{user:username}/terima', [ChatController::class, 'accept'])->name('chat.accept');
    Route::post('/obrolan/{user:username}/tolak', [ChatController::class, 'decline'])->name('chat.decline');
    Route::delete('/obrolan/{user:username}/bersihkan', [ChatController::class, 'clearConversation'])->name('chat.clear');
    Route::delete('/obrolan/{user:username}/hapus', [ChatController::class, 'destroyConversation'])->name('chat.destroy');
});

// Spaces (live audio/video)
Route::get('/spaces', [SpaceController::class, 'index'])->name('spaces.index');
Route::middleware('auth')->group(function () {
    Route::post('/spaces', [SpaceController::class, 'store'])->name('spaces.store');
    Route::get('/spaces/{slug}', [SpaceController::class, 'show'])->name('spaces.show');
    Route::post('/spaces/{space:slug}/join', [SpaceController::class, 'join'])->name('spaces.join');
    Route::post('/spaces/{space:slug}/leave', [SpaceController::class, 'leave'])->name('spaces.leave');
    Route::post('/spaces/{space:slug}/end', [SpaceController::class, 'end'])->name('spaces.end');
    Route::post('/spaces/{space:slug}/signal', [SpaceController::class, 'signal'])->name('spaces.signal');
});

// Infinite-scroll feed fragments (HTML partials)
Route::get('/feed-fragment/projects', [InfiniteFeedController::class, 'projectFeed'])->name('feeds.projects');
Route::get('/feed-fragment/bookmarks', [InfiniteFeedController::class, 'bookmarkFeed'])->name('feeds.bookmarks');

// Developer profile & project showcase (public)
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/project/{slug}', [ProjectController::class, 'show'])->name('projects.show');

// Communities exploration (public read-only)
Route::get('/komunitas', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/komunitas/{slug}', [CommunityController::class, 'show'])->name('communities.show');
Route::get('/feed-fragment/communities', [InfiniteFeedController::class, 'communityFeed'])->name('feeds.communities');
Route::get('/feed-fragment/community/{community:slug}', [InfiniteFeedController::class, 'communityShowFeed'])->name('feeds.community');

Route::middleware('auth')->group(function () {
    // Project lifecycle (upload, edit, update, delete)
    Route::get('/unggah', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/unggah', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/project/{project:slug}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/project/{project:slug}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/project/{project:slug}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Project interactions
    Route::post('/project/{project}/vote', [ProjectController::class, 'vote'])->name('projects.vote');
    Route::post('/project/{project}/comment', [ProjectController::class, 'comment'])->name('projects.comment');
    Route::delete('/project/komentar/{comment}', [ProjectController::class, 'destroyComment'])->name('projects.comment.destroy');
    Route::post('/project/{project}/bookmark', [ProjectController::class, 'bookmark'])->name('projects.bookmark');
    Route::get('/koleksi', [ProjectController::class, 'bookmarks'])->name('projects.bookmarks');

    // Social graph: follow / unfollow
    Route::post('/u/{user:username}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Profile & account settings
    Route::put('/pengaturan/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/pengaturan/keamanan', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

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

    // User reporting
    Route::post('/laporan', [ReportController::class, 'store'])->name('reports.store');
});

// Admin Moderation routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/laporan', [AdminModerationController::class, 'index'])->name('reports.index');
    Route::get('/pengguna', [AdminModerationController::class, 'index'])->name('users.index');
    Route::post('/laporan/{report}/resolve', [AdminModerationController::class, 'resolveReport'])->name('reports.resolve');
    Route::post('/laporan/{report}/dismiss', [AdminModerationController::class, 'dismissReport'])->name('reports.dismiss');

    Route::post('/project/{project}/warning', [AdminModerationController::class, 'warnProject'])->name('projects.warn');
    Route::delete('/project/{project}', [AdminModerationController::class, 'destroyProject'])->name('projects.destroy');

    Route::post('/user/{user}/warning', [AdminModerationController::class, 'warnUser'])->name('users.warn');
    Route::post('/user/{user}/ban', [AdminModerationController::class, 'banUser'])->name('users.ban');
    Route::post('/user/{user}/unban', [AdminModerationController::class, 'unbanUser'])->name('users.unban');
    Route::delete('/user/{user}', [AdminModerationController::class, 'destroyUser'])->name('users.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    // Password reset
    Route::get('/lupa-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

    // Email verification
    Route::get('/email/verifikasi', [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verifikasi/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/email/kirim-ulang', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

// Google OAuth — harus di luar middleware guest agar callback tidak terblokir
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::redirect('/masuk', '/login');
Route::redirect('/daftar', '/register');
