<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * 后台首页：统计 + 最近文章（仅当前用户，含草稿）
     */
    public function index(): View
    {
        $notes = Note::with(['user', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $notesCount = Note::count();
        $categoriesCount = Category::count();
        $tagsCount = Tag::count();
        $heroImage = SiteSetting::get('hero_image');
        $aboutMarkdown = SiteSetting::get('about_markdown', '');

        return view('dashboard', compact('notes', 'notesCount', 'categoriesCount', 'tagsCount', 'heroImage', 'aboutMarkdown'));
    }

    /**
     * 获取 About 页面 Markdown 内容
     */
    public function getAboutContent(): JsonResponse
    {
        $markdown = SiteSetting::get('about_markdown', '');
        return response()->json(['markdown' => $markdown]);
    }

    /**
     * 保存 About 页面 Markdown 内容
     */
    public function updateAboutContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'markdown' => 'nullable|string|max:50000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->toArray()], 422);
        }

        SiteSetting::set('about_markdown', $request->input('markdown', ''));

        return response()->json(['message' => 'About 内容已更新']);
    }

    /**
     * 更新首页 Hero 背景图
     *
     * 注意：web 路由的 validate() 默认返回 302 重定向而非 JSON，
     * 所以手动 Validator::make + 显式 response()->json()，确保前端 fetch 能拿到错误。
     * max:20480 对齐 PHP upload_max_filesize（10M），设置为 20M 覆盖高清大图场景。
     * 如果 PHP 层面限制更小（如 2M），需要在 php.ini 中调大 upload_max_filesize 和 post_max_size。
     */
    public function updateHeroImage(Request $request): JsonResponse
    {
        // 移除背景图
        if ($request->boolean('remove')) {
            $oldPath = SiteSetting::get('hero_image');
            SiteSetting::set('hero_image', null);
            if ($oldPath && str_starts_with($oldPath, '/storage/uploads/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
            }
            return response()->json(['url' => null, 'message' => 'Hero 背景图已移除']);
        }

        // 手动校验（web 路由必须显式返回 JSON 422，否则 validate() 返回 302）
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:20480',
        ], [
            'image.required' => '请选择图片文件',
            'image.image'   => '文件必须是有效图片',
            'image.mimes'  => '仅支持 JPEG、PNG、WebP、GIF 格式',
            'image.max'    => '图片大小不能超过 20MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->toArray()], 422);
        }

        // 存储文件（try-catch 兜底磁盘/权限异常）
        try {
            $path = $request->file('image')->store('uploads', 'public');
        } catch (\Exception $e) {
            report($e); // 记录到日志但不暴露细节
            return response()->json(['errors' => ['image' => ['上传失败，请稍后重试']]], 500);
        }

        $url = '/storage/' . ltrim($path, '/');

        // 删除旧图
        $oldPath = SiteSetting::get('hero_image');
        if ($oldPath && str_starts_with($oldPath, '/storage/uploads/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
        }

        SiteSetting::set('hero_image', $url);

        return response()->json(['url' => $url, 'message' => 'Hero 背景图更新成功']);
    }
}
