<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutosaveNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 路由已有 auth 中间件
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:notes,id',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'slug' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_cover' => 'nullable|boolean',
        ];
    }
}
