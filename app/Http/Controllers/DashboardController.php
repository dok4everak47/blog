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
        $notes = Note::forUser()
            ->latest()
            ->take(5)
            ->get();

        $notesCount = Note::forUser()->count();
        $categoriesCount = Category::count();
        $tagsCount = Tag::count();
        $heroImage = SiteSetting::get('hero_image');
        $aboutContent = SiteSetting::get('about_content', []);

        return view('dashboard', compact('notes', 'notesCount', 'categoriesCount', 'tagsCount', 'heroImage', 'aboutContent'));
    }

    /**
     * 获取 About 页面内容配置
     */
    public function getAboutContent(): JsonResponse
    {
        $content = SiteSetting::get('about_content', [
            'greeting' => 'hello，很高兴遇见你，陌生人。相见即是幸运，那下面是关于我的一些介绍 😊',
            'self' => [
                ['icon' => '👋', 'label' => '称呼', 'value' => '你可以称我为 Dok4ever 或 4ever'],
                ['icon' => '🎓', 'label' => '身份', 'value' => '应届毕业生 / Laravel 学习者'],
                ['icon' => '🌍', 'label' => '坐标', 'value' => '中国 · 广西'],
                ['icon' => '🌱', 'label' => '目前状态', 'value' => '正在从零学习 Laravel'],
                ['icon' => '🏷️', 'label' => '标签', 'value' => 'Laravel 初学者 | PHP 新手 | 技术爱好者'],
            ],
            'tech' => [
                ['label' => '后端框架', 'value' => 'Laravel 13（正在深入学习中～）'],
                ['label' => '前端技术', 'value' => 'Tailwind CSS v4 · Alpine.js · Vite'],
                ['label' => '语言基础', 'value' => 'PHP 8.3 · 正在补齐 JavaScript 和 SQL'],
                ['label' => '目标', 'value' => '把 Laravel 学精，做出有个人风格的博客系统 ✨'],
            ],
            'contact_intro' => "如果想交换友链可以去友链界面；\n和我临时聊天可以去留言板；\n如有任何问题欢迎给我发邮件。",
            'email' => 'girlsfrontline45@gmail.com',
        ]);

        return response()->json($content);
    }

    /**
     * 保存 About 页面内容配置
     */
    public function updateAboutContent(Request $request): JsonResponse
    {
        $data = $validator = Validator::make($request->all(), [
            'greeting' => 'nullable|string|max:500',
            'self' => 'nullable|array|max:10',
            'self.*.icon' => 'nullable|string|max:10',
            'self.*.label' => 'nullable|string|max:50',
            'self.*.value' => 'nullable|string|max:300',
            'tech' => 'nullable|array|max:10',
            'tech.*.label' => 'nullable|string|max:50',
            'tech.*.value' => 'nullable|string|max:500',
            'contact_intro' => 'nullable|string|max:1000',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->toArray()], 422);
        }

        SiteSetting::set('about_content', $request->all());

        return response()->json(['message' => 'About 内容已更新']);
    }

    /**
     * 更新首页 Hero 背景图
     *
     * 注意：web 路由的 validate() 默认返回 302 重定向而非 JSON，
     * 所以手动 Validator::make + 显式 response()->json()，确保前端 fetch 能拿到错误。
     * max:2048 对齐 PHP upload_max_filesize（默认 2M），避免 PHP 静默拦截大文件。
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
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ], [
            'image.required' => '请选择图片文件',
            'image.image'   => '文件必须是有效图片',
            'image.mimes'  => '仅支持 JPEG、PNG、WebP、GIF 格式',
            'image.max'    => '图片大小不能超过 10MB',
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
