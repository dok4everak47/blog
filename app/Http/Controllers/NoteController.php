<?php

namespace App\Http\Controllers;

use App\Enums\NoteStatus;
use App\Http\Requests\AutosaveNoteRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Category;
use App\Models\Note;
use App\Models\Tag;
use App\Services\CoverImageService;
use App\Services\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(
        private readonly CoverImageService $covers,
        private readonly ImageProcessor $images,
    ) {}

    /**
     * 文章列表（公开访问，分页）
     */
    public function index(): View
    {
        $notes = Note::published()
            ->with('tags', 'category')
            ->latest()
            ->paginate(9);

        return view('notes.index', compact('notes'));
    }

    /**
     * 写文章表单（后台，需登录）
     */
    public function create(): View
    {
        $tags = Tag::withCount('notes')->get();
        $categories = Category::withCount('notes')->get();

        return view('notes.create', compact('tags', 'categories'));
    }

    /**
     * 保存新文章
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $this->authorize('create', Note::class);

        $cover = $request->hasFile('cover_image')
            ? $this->covers->upload($request->file('cover_image'))
            : ['cover' => null, 'thumbnail' => null];

        $status = $request->input('status', 'published');

        $note = auth()->user()->notes()->create([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->filled('excerpt') ? $request->excerpt : Note::generateExcerpt($request->content),
            'category_id' => $request->category_id,
            'slug' => $this->makeSlug($request->slug, $request->title, null),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
            'cover_image' => $cover['cover'],
            'thumbnail_url' => $cover['thumbnail'],
        ]);

        if ($request->has('tags')) {
            $note->tags()->attach($request->tags);
        }

        Cache::forget('feed.sitemap');

        return redirect()
            ->route('dashboard')
            ->with('success', '文章已发布！');
    }

    /**
     * 文章详情（公开访问）
     * 草稿仅登录用户（作者）可见，未登录访问草稿 → 404
     */
    public function show(Note $note): View
    {
        // Policy 双重校验：已发布→放行，草稿→仅作者（含 403/404）
        $this->authorize('view', $note);

        $note->loadMissing(['tags', 'category', 'user', 'comments.user', 'comments.replies.user']);

        // 阅读统计：用 session 防刷（同一会话内不重复计数）
        $viewKey = "viewed_note_{$note->id}";
        if (! session($viewKey)) {
            $note->increment('views');
            session([$viewKey => true]);
        }

        // 上一篇 / 下一篇（仅已发布）
        $previous = Note::published()->where('id', '<', $note->id)->latest('id')->first();
        $next = Note::published()->where('id', '>', $note->id)->oldest('id')->first();

        // 相关文章（基于共同标签匹配，排除自身，取 5 篇）
        $relatedNotes = $note->related();

        return view('notes.show', compact('note', 'previous', 'next', 'relatedNotes'));
    }

    /**
     * 编辑文章表单
     */
    public function edit(Note $note): View
    {
        $this->authorize('update', $note);

        $tags = Tag::withCount('notes')->get();
        $categories = Category::withCount('notes')->get();

        return view('notes.edit', compact('note', 'tags', 'categories'));
    }

    /**
     * 更新文章
     */
    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $status = $request->input('status', $note->status?->value ?? 'published');

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->filled('excerpt') ? $request->excerpt : Note::generateExcerpt($request->content),
            'category_id' => $request->category_id,
            'slug' => $this->makeSlug($request->slug, $request->title, $note->id),
            'status' => $status,
        ];

        // 草稿转发布时设置 published_at；发布转草稿时保留原 published_at
        if ($status === 'published' && ! $note->published_at) {
            $data['published_at'] = now();
        }

        // 封面图：上传新图 / 移除旧图（无操作时返回 null，跳过更新）
        $cover = $this->covers->apply(
            $note->cover_image,
            $note->getRawOriginal('thumbnail_url'),
            $request->hasFile('cover_image') ? $request->file('cover_image') : null,
            $request->boolean('remove_cover'),
        );
        if ($cover !== null) {
            $data['cover_image'] = $cover['cover'];
            $data['thumbnail_url'] = $cover['thumbnail'];
        }

        $note->update($data);

        $note->tags()->sync($request->tags ?? []);

        Cache::forget('feed.sitemap');

        return redirect()
            ->route('dashboard')
            ->with('success', '文章已更新！');
    }

    /**
     * 自动保存 / 存草稿（后台静默保存，返回 JSON）
     * 用于编辑器实时保存，避免意外丢失内容。
     */
    public function autosave(AutosaveNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $attrs = [
            'title' => $data['title'] ?? '',
            'content' => $data['content'] ?? '',
            'excerpt' => Note::generateExcerpt($data['content'] ?? ''),
            'category_id' => $data['category_id'] ?? null,
            'slug' => $this->makeSlug($data['slug'] ?? null, $data['title'] ?? '', $data['id'] ?? null),
        ];

        if (! empty($data['id'])) {
            // 更新已有文章（保留原状态，autosave 不会把已发布文章降级为草稿）
            $note = Note::find($data['id']);
            $this->authorize('update', $note);

            $cover = $this->covers->apply(
                $note->cover_image,
                $note->getRawOriginal('thumbnail_url'),
                $request->hasFile('cover_image') ? $request->file('cover_image') : null,
                $request->boolean('remove_cover'),
            );
            if ($cover !== null) {
                $attrs['cover_image'] = $cover['cover'];
                $attrs['thumbnail_url'] = $cover['thumbnail'];
            }

            $note->update($attrs);
            $note->tags()->sync($data['tags'] ?? []);
        } else {
            // 首次自动保存：创建一条草稿
            $this->authorize('create', Note::class);

            if ($request->hasFile('cover_image')) {
                $cover = $this->covers->upload($request->file('cover_image'));
                $attrs['cover_image'] = $cover['cover'];
                $attrs['thumbnail_url'] = $cover['thumbnail'];
            }
            $attrs['status'] = NoteStatus::Draft->value;
            $note = auth()->user()->notes()->create($attrs);
            $note->tags()->sync($data['tags'] ?? []);
        }

        return response()->json([
            'id' => $note->id,
            'saved_at' => $note->updated_at->timestamp,
            'cover_url' => $note->cover_image_url,
        ]);
    }

    /**
     * 编辑器内联图片上传（本地文件 → /storage/uploads）
     * 仅供已登录用户使用，配合工具栏「插入图片」弹窗。
     * 显式返回 JSON（含验证失败 422），不依赖全局 shouldRenderJsonWhen('api/*') 配置。
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $result = $this->images->upload($request->all(), 'uploads', 10240);

        if (isset($result['error'])) {
            return response()->json(['errors' => $result['error']], 422);
        }

        return response()->json([
            'url' => '/storage/'.ltrim($result['path'], '/'),
        ]);
    }

    /**
     * 后台快速更换封面图（Dashboard 列表弹窗调用，返回 JSON）
     * 仅更新 cover_image 字段，不触碰正文/标题等其它内容。
     */
    public function updateCover(Request $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);

        $validator = Validator::make($request->all(), [
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_cover' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 无操作：既没有上传新图也没有勾选移除
        $cover = $this->covers->apply(
            $note->cover_image,
            $note->getRawOriginal('thumbnail_url'),
            $request->hasFile('cover_image') ? $request->file('cover_image') : null,
            $request->boolean('remove_cover'),
        );
        if ($cover === null) {
            return response()->json([
                'cover_url' => $note->cover_image_url,
                'message' => '未变更',
            ]);
        }

        $note->update([
            'cover_image' => $cover['cover'],
            'thumbnail_url' => $cover['thumbnail'],
        ]);

        return response()->json([
            'cover_url' => $note->cover_image_url,
            'message' => '封面已更新',
        ]);
    }

    /**
     * 生成唯一 Slug：优先使用用户填写的 slug，其次从标题生成。
     */
    private function makeSlug(?string $slug, string $title, ?int $ignoreId): ?string
    {
        $base = $slug ?: Str::slug($title);

        if (! $base) {
            return null;
        }

        $original = $base;
        $i = 1;

        while (Note::where('slug', $base)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $base = $original.'-'.$i++;
        }

        return $base;
    }

    /**
     * 删除文章
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $this->covers->remove(
            $note->cover_image,
            $note->getRawOriginal('thumbnail_url'),
        );

        $note->delete();

        Cache::forget('feed.sitemap');

        return redirect()
            ->route('dashboard')
            ->with('success', '文章已删除。');
    }
}
