<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // 安全响应头
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        // 文章浏览统计
        $middleware->appendToGroup('web', \App\Http\Middleware\RecordPageView::class);

        // 测试环境禁用 CSRF 校验：Laravel 13 在测试中不会自动跳过 CSRF（
        // phpunit.xml 的 <env> 不一定调用 putenv()，且 Laravel app 在 PHPUnit
        // 注入前已 boot）。必须由调用方 OS 级传 APP_ENV=testing（如
        // `APP_ENV=testing php artisan test`），此处 getenv() 才能拿到 'testing'。
        if (getenv('APP_ENV') === 'testing') {
            $middleware->validateCsrfTokens(except: ['*']);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
