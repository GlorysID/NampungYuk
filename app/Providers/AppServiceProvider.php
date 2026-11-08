<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Community;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifikasi Alamat Email — NampungYuk')
                ->greeting('Halo, '.$notifiable->name.'!')
                ->line('Terima kasih telah mendaftar di NampungYuk. Silakan klik tombol di bawah ini untuk memverifikasi alamat email kamu dan mengaktifkan akun.')
                ->action('Verifikasi Email Sekarang', $url)
                ->line('Tautan verifikasi ini berlaku selama 60 menit.')
                ->line('Jika kamu tidak pernah mendaftar di NampungYuk, silakan abaikan email ini.');
        });

        View::composer(['layouts.app', 'partials.sidebar'], function ($view) {
            $request = request();
            $cached = $request?->attributes->get('__sidebar_composer_data');

            if ($cached === null) {
                $categories = Schema::hasTable('categories')
                    ? Category::withCount('projects')->orderBy('name')->get()
                    : collect();

                $communities = Schema::hasTable('communities')
                    ? Community::visibleTo(Auth::id())->orderByDesc('members_count')->take(5)->get()
                    : collect();

                /** @var User|null $user */
                $user = Auth::user();
                $pendingReportsCount = ($user?->isAdmin() && Schema::hasTable('reports'))
                    ? Report::pending()->count()
                    : 0;

                $defaultRailTrending = Schema::hasTable('projects')
                    ? Cache::remember('rail:trending_projects_v2', 300, fn () => Project::query()
                        ->visibleTo(null)
                        ->with('user:id,name,username')
                        ->orderByDesc('score')
                        ->take(5)
                        ->get()
                        ->map(fn (Project $p) => [
                            'slug' => $p->slug,
                            'title' => $p->title,
                            'thumbnail' => $p->thumbnail,
                            'score' => (int) $p->score,
                            'username' => $p->user?->username ?? 'anon',
                        ])
                        ->all())
                    : [];

                $cached = [
                    'sidebarCategories' => $categories,
                    'sidebarCommunities' => $communities,
                    'pendingReportsCount' => $pendingReportsCount,
                    'defaultRailTrending' => $defaultRailTrending,
                ];

                if ($request) {
                    $request->attributes->set('__sidebar_composer_data', $cached);
                }
            }

            $view->with($cached);
        });
    }
}
