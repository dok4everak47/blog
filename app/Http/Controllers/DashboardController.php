<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * 后台首页：统计 + 最近文章（仅当前用户，含草稿）
     */
    public function index(): View
    {
        $notes = Note::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        $notesCount = Note::where('user_id', auth()->id())->count();
        $categoriesCount = Category::count();
        $tagsCount = Tag::count();
        $heroImage = SiteSetting::get('hero_image');

        return view('dashboard', compact('notes', 'notesCount', 'categoriesCount', 'tagsCount', 'heroImage'));
    }

    /**
     * 更新首页 Hero 背景图
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

        // 上传新图
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120']);

        $path = $request->file('image')->store('uploads', 'public');
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
