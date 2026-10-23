<?php

namespace App\Providers;

use App\Models\CompanyProfile;
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
        // Dipakai di layout publik (footer: about, kontak) — supaya tidak perlu
        // di-pass manual dari tiap controller.
        View::composer(['layouts.public', 'home', 'portfolio.*', 'requests.*'], function ($view) {
            $view->with('profile', CompanyProfile::current());
        });
    }
}
