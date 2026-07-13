<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * 搜索已发布文章（标题 + 正文，模糊匹配）
     */
    public function index(Request $request): View
    {
        $q = trim($request->input('q', ''));

        $notes = Note::published()
            ->when($q !== '', function ($query) use ($q) {
                // 转义 LIKE 通配符，避免 % 和 _ 被当作通配符
                $escapedQ = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
                $query->where(function ($sub) use ($escapedQ) {
                    $sub->where('title', 'like', "%{$escapedQ}%")
                        ->orWhere('content', 'like', "%{$escapedQ}%");
                });
            })
            ->with('tags', 'category')
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('search', compact('q', 'notes'));
    }
}
