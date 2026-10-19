<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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
            if (Schema::hasTable('categories')) {
                $categories = Category::withCount('projects')
                    ->orderBy('name')
                    ->get();
                $view->with('sidebarCategories', $categories);
            }
        });
    }
}
