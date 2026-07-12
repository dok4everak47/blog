<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'nullable|in:draft,published,archived',
            'slug' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_cover' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '标题不能为空',
            'title.max' => '标题最多 255 个字符',
            'content.required' => '内容不能为空',
            'category_id.exists' => '分类不存在',
            'tags.*.exists' => '标签不存在',
        ];
    }
}
