<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * 首页：Hero + 最新 6 篇 + 分类/标签统计
     *
     * 注意：此处不使用 Cache::remember 缓存 Eloquent Collection。
     * database 驱动的缓存会 serialize 整个模型（含关联），
     * 反序列化时容易产生 __PHP_Incomplete_Class 错误。
     * 如需缓存，建议用 Redis/Memcached 或在 Controller 外层做 HTTP 缓存。
     */
    public function index(): View
    {
        $notes = Note::published()
            ->with('tags', 'category')
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::withCount('notes')->get();
        $tags = Tag::withCount('notes')->get();

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
