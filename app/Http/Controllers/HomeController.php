<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * 首页：精选 + 最新 6 篇 + 分类/标签统计
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
