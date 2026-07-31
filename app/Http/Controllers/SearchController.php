<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * 搜索已发布文章（标题 + 正文，PostgreSQL pg_trgm 相似度搜索）
     *
     * - 多关键词按空格拆分，每个词都需匹配（标题或正文）
     * - 标题命中权重 ×3，正文命中权重 ×1，按加权相似度排序
     */
    public function index(Request $request): View
    {
        $q = trim($request->input('q', ''));

        $notes = Note::published()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    foreach (preg_split('/\s+/', $q) as $word) {
                        $sub->where(function ($wq) use ($word) {
                            $wq->whereRaw('similarity(lower(title), ?) > 0.2', [mb_strtolower($word)])
                               ->orWhereRaw('similarity(lower(content), ?) > 0.2', [mb_strtolower($word)]);
                        });
                    }
                })
                ->orderByRaw('
                    (similarity(lower(title), ?) * 3 + similarity(lower(content), ?)) DESC
                ', [mb_strtolower($q), mb_strtolower($q)]);
            })
            ->with('tags', 'category')
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('search', compact('q', 'notes'));
    }
}
