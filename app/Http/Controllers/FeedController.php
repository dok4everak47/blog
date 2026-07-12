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
     */
    public function rss(): Response
    {
        $notes = Cache::remember('feed.rss', 1800, function () {
            return Note::query()
                ->published()
                ->with('category', 'tags')
                ->latest('published_at')
                ->limit(20)
                ->get();
        });

        $xml = view('feeds.rss', compact('notes'))->render();

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Sitemap XML — 所有已发布文章 + 首页
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('feed.sitemap', 3600, function () {
            // 首页（最高优先级）
            $urls = collect([[
                'loc' => URL::route('home'),
                'lastmod' => Note::published()->max('updated_at') ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ]]);

            // 所有已发布文章
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
