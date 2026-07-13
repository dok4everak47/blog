<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * 分类下的文章列表（公开访问，分页）
     */
    public function show(Category $category): View
    {
        $notes = $category->notes()
            ->published()
            ->with('tags', 'category')
            ->latest()
            ->paginate(9);

        return view('categories.show', compact('category', 'notes'));
    }

    /**
     * 内联快速创建分类（编辑器内「+ 新建分类」）。
     * 返回 JSON 供前端动态插入 <option> 并选中。
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:categories,name',
        ], [
            'name.required' => '分类名称不能为空',
            'name.max' => '分类名称最多 50 个字符',
            'name.unique' => '已存在同名分类',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->toArray()], 422);
        }

        $category = Category::create($validator->validated());

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }
}
