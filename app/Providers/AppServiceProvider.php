<?php

namespace App\Providers;

use App\Models\CompanyProfile;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Pembatas percobaan per alamat IP (butuh trustProxies agar IP asli terbaca).
        RateLimiter::for('admin-login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(30)->by($request->ip()),
            ];
        });

        RateLimiter::for('public-form', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perHour(15)->by($request->ip()),
            ];
        });

        // Dipakai di layout publik (footer: about, kontak) — supaya tidak perlu
        // di-pass manual dari tiap controller.
        View::composer(['layouts.public', 'home', 'portfolio.*', 'requests.*'], function ($view) {
            $view->with('profile', CompanyProfile::current());
        });
    }
}
