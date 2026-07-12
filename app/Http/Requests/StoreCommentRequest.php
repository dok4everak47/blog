<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 路由已有 auth 中间件保护
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => '评论内容不能为空',
            'content.max' => '评论内容最多 2000 个字符',
            'parent_id.exists' => '父评论不存在',
        ];
    }
}
