<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // 生产环境强制 HTTPS，避免 mixed content 和 session cookie 泄漏
        // 本地开发环境保持 HTTP，避免 vite HMR 失效
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
