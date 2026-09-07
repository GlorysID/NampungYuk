<?php

namespace App\Providers;

use App\Models\Category;
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
