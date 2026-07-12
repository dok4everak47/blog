<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

    /**
     * 快速创建标签（编辑器内联调用，自动去重）
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tags', 'name')->whereNull('deleted_at'),
            ],
        ], [
            'name.required' => '标签名不能为空',
            'name.max'      => '标签名最多 30 个字符',
            'name.unique'   => '该标签已存在',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->toArray()], 422);
        }

        $name = trim($request->input('name'));

        // 如果已存在则直接返回，避免重复创建
        $tag = Tag::firstOrCreate(['name' => $name]);

        return response()->json(['id' => $tag->id, 'name' => $tag->name]);
    }
}
