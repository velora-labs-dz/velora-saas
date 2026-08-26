<?php

namespace App\Providers;

use App\Support\CurrentOrganization;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // CurrentOrganization is shared, request-scoped state: it's set
        // once by ResolveCurrentOrganization and then read by everything
        // downstream in the same request — EnsureCurrentOrganization,
        // controllers, policies, form requests. Without an explicit
        // singleton binding, Laravel's container hands out a fresh,
        // never-populated instance to each of those on every resolution,
        // so nothing downstream ever sees what the middleware set. See
        // docs/PROGRESS.md's Step 4 session log for how this surfaced.
        $this->app->singleton(CurrentOrganization::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
