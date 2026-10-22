<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dashboard\StatsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 博客前台路由（公开访问）
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// RSS Feed + Sitemap（公开）
Route::get('/feed.xml', [FeedController::class, 'rss'])->name('feed.rss');
Route::get('/sitemap.xml', [FeedController::class, 'sitemap'])->name('feed.sitemap');

// 文章列表（公开）
Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');

// 搜索
Route::get('/search', [SearchController::class, 'index'])->name('search');

// 分类页 / 标签页
Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->whereNumber('category')
    ->name('categories.show');
Route::get('/tags/{tag}', [TagController::class, 'show'])
    ->whereNumber('tag')
    ->name('tags.show');

// 文章详情 — 公开访问（草稿仅作者可见，Controller 内已过滤）
// whereNumber 约束只匹配数字 id，避免与 /notes/create 冲突
Route::get('/notes/{note}', [NoteController::class, 'show'])
    ->whereNumber('note')
    ->name('notes.show');

/*
|--------------------------------------------------------------------------
| 后台路由（需要登录）
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [StatsController::class, 'index'])
        ->middleware('admin')
        ->name('dashboard.stats');

    // 站点全局设置（仅管理员）
    Route::middleware('admin')->group(function () {
        // 首页 Hero 背景图设置
        Route::post('/settings/hero-image', [DashboardController::class, 'updateHeroImage'])
            ->middleware('throttle:30,1')
            ->name('settings.hero-image');

        // About 页面内容编辑
        Route::get('/settings/about-content', [DashboardController::class, 'getAboutContent'])
            ->name('settings.about-content.get');
        Route::post('/settings/about-content', [DashboardController::class, 'updateAboutContent'])
            ->middleware('throttle:30,1')
            ->name('settings.about-content.update');

        // 编辑器内联快速创建分类
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });

    // 文章管理
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // 后台快速更换封面图（Dashboard 列表弹窗，仅更新封面字段）
    Route::post('/notes/{note}/cover', [NoteController::class, 'updateCover'])
        ->middleware('throttle:30,1')
        ->name('notes.updateCover');

    // 自动保存（限流防滥用）
    Route::post('/notes/autosave', [NoteController::class, 'autosave'])
        ->middleware('throttle:30,1')
        ->name('notes.autosave');

    // 编辑器内联图片上传（本地文件 → /storage/uploads，限流防滥用）
    Route::post('/notes/upload-image', [NoteController::class, 'uploadImage'])
        ->middleware('throttle:30,1')
        ->name('notes.upload-image');

    // 编辑器内联快速创建标签
    Route::post('/tags', [TagController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('tags.store');

    // 个人资料
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 评论（需登录）
    Route::post('/notes/{note}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // 点赞（需登录）
    Route::post('/notes/{note}/reactions', [ReactionController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('notes.reactions.toggle');
});

/*
|--------------------------------------------------------------------------
| Breeze 认证路由
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
