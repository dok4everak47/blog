<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * 标签下的文章列表（公开访问，分页）
     */
    public function show(Tag $tag): View
    {
        $notes = $tag->notes()
            ->published()
            ->with('tags', 'category')
            ->latest()
            ->paginate(9);

        return view('tags.show', compact('tag', 'notes'));
    }
}
