<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * 首页：精选 + 最新 6 篇 + 分类/标签统计
     */
    public function index(): View
    {
        $notes = Cache::remember('home.notes', 300, function () {
            return Note::published()
                ->with('tags', 'category')
                ->latest()
                ->take(6)
                ->get();
        });

        $categories = Cache::remember('home.categories', 600, function () {
            return Category::withCount('notes')->get();
        });

        $tags = Cache::remember('home.tags', 600, function () {
            return Tag::withCount('notes')->get();
        });

        return view('home', compact('notes', 'categories', 'tags'));
    }

    /**
     * 关于页
     */
    public function about(): View
    {
        return view('profile');
    }

    /**
     * 联系页
     */
    public function contact(): View
    {
        return view('contact');
    }
}
