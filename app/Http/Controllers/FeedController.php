<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class FeedController extends Controller
{
    /**
     * RSS 2.0 Feed — 最新 20 篇已发布文章
     *
     * 不缓存 Eloquent Collection（database 驱动序列化会产生 __PHP_Incomplete_Class）。
     * RSS 本身可由 CDN/代理层缓存，或后续换 Redis 后再加 Cache::remember。
     */
    public function rss(): Response
    {
        $notes = Note::query()
            ->published()
            ->with('category', 'tags')
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $xml = view('feeds.rss', compact('notes'))->render();

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Sitemap XML — 所有已发布文章 + 首页
     *
     * 此处缓存安全：存的是渲染后的 XML 字符串，非 Eloquent 对象。
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('feed.sitemap', 86400, function () {
            // 首页（最高优先级）
            $urls = collect([[
                'loc' => URL::route('home'),
                'lastmod' => Note::published()->max('updated_at') ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ]]);

            // 所有已发布文章（只查需要字段，不加载关联）
            Note::query()
                ->published()
                ->select(['id', 'slug', 'updated_at', 'created_at'])
                ->latest()
                ->chunk(200, function ($notes) use (&$urls) {
                    foreach ($notes as $note) {
                        $urls->push([
                            'loc' => URL::route('notes.show', $note),
                            'lastmod' => $note->updated_at->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority' => '0.8',
                        ]);
                    }
                });

            return view('feeds.sitemap', ['urls' => $urls])->render();
        });

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
