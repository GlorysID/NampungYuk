<?php

use App\Http\Middleware\CheckBanned;
use App\Http\Middleware\EnsureIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CheckBanned::class,
        ]);
        $middleware->alias([
            'admin' => EnsureIsAdmin::class,
        ]);

        // Guests stay on their current page with the global auth modal open instead
        // of being sent to the standalone /login page.
        $middleware->redirectGuestsTo(function (Request $request): string {
            $previous = url()->previous();
            $sameHost = parse_url($previous, PHP_URL_HOST) === $request->getHost();
            $isLoop = $previous === $request->fullUrl()
                || str_contains($previous, '/login')
                || str_contains($previous, '/register');

            if (! $sameHost || $isLoop) {
                $previous = route('projects.index');
            }

            $request->session()->flash('auth_required', true);

            return $previous;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
