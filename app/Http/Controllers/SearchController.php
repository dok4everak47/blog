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
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->with('tags', 'category')
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('search', compact('q', 'notes'));
    }
}
