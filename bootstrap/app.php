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

        // 通过实际 OS 环境变量判断是否测试环境（phpunit.xml 的 <env> 不一定调用 putenv）
        if (getenv('APP_ENV') === 'testing') {
            $middleware->validateCsrfTokens(except: ['*']);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
