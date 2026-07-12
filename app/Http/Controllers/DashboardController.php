<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * 后台首页：统计 + 最近文章（含草稿）
     */
    public function index(): View
    {
        $notes = Note::latest()->take(5)->get();
        $notesCount = Note::count();
        $categoriesCount = Category::count();
        $tagsCount = Tag::count();

        return view('dashboard', compact('notes', 'notesCount', 'categoriesCount', 'tagsCount'));
    }
}
