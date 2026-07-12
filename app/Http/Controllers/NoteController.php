<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use App\Models\Category;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NoteController extends Controller
{
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
            ? $request->file('cover_image')->store('covers', 'public')
            : null;

        $note = auth()->user()->notes()->create([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'slug' => $this->makeSlug($request->slug, $request->title, null),
            'status' => $request->input('status', 'published'),
            'cover_image' => $cover,
        ]);

        if ($request->has('tags')) {
            $note->tags()->attach($request->tags);
        }

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
        // 草稿仅作者本人可见；未登录或非作者访问 → 404
        if ($note->isDraft() && (!auth()->check() || $note->user_id !== auth()->id())) {
            abort(404);
        }

        $note->load('tags', 'category');

        // 上一篇 / 下一篇（仅已发布）
        $previous = Note::published()->where('id', '<', $note->id)->latest('id')->first();
        $next = Note::published()->where('id', '>', $note->id)->oldest('id')->first();

        // 相关文章（同分类或共享标签，排除自身，取 3 篇）
        $related = Note::published()->where('id', '!=', $note->id)
            ->when($note->category_id, fn ($q) => $q->where('category_id', $note->category_id))
            ->with('category')
            ->latest()
            ->take(3)
            ->get();

        return view('notes.show', compact('note', 'previous', 'next', 'related'));
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

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'slug' => $this->makeSlug($request->slug, $request->title, $note->id),
            'status' => $request->input('status', $note->status),
        ];

        // 封面图：上传新图 / 移除旧图
        if ($request->hasFile('cover_image')) {
            if ($note->cover_image) {
                Storage::disk('public')->delete($note->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        } elseif ($request->boolean('remove_cover')) {
            if ($note->cover_image) {
                Storage::disk('public')->delete($note->cover_image);
            }
            $data['cover_image'] = null;
        }

        $note->update($data);

        $note->tags()->sync($request->tags ?? []);

        return redirect()
            ->route('dashboard')
            ->with('success', '文章已更新！');
    }

    /**
     * 自动保存 / 存草稿（后台静默保存，返回 JSON）
     * 用于编辑器实时保存，避免意外丢失内容。
     */
    public function autosave(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:notes,id',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'slug' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_cover' => 'nullable|boolean',
        ]);

        $attrs = [
            'title' => $data['title'] ?? '',
            'content' => $data['content'] ?? '',
            'category_id' => $data['category_id'] ?? null,
            'slug' => $this->makeSlug($data['slug'] ?? null, $data['title'] ?? '', $data['id'] ?? null),
        ];

        if (!empty($data['id'])) {
            // 更新已有文章（保留原状态，autosave 不会把已发布文章降级为草稿）
            $note = Note::find($data['id']);
            $this->authorize('update', $note);

            if ($request->hasFile('cover_image')) {
                if ($note->cover_image) {
                    Storage::disk('public')->delete($note->cover_image);
                }
                $attrs['cover_image'] = $request->file('cover_image')->store('covers', 'public');
            } elseif ($request->boolean('remove_cover')) {
                if ($note->cover_image) {
                    Storage::disk('public')->delete($note->cover_image);
                }
                $attrs['cover_image'] = null;
            }

            $note->update($attrs);
            $note->tags()->sync($data['tags'] ?? []);
        } else {
            // 首次自动保存：创建一条草稿
            $this->authorize('create', Note::class);

            if ($request->hasFile('cover_image')) {
                $attrs['cover_image'] = $request->file('cover_image')->store('covers', 'public');
            }
            $attrs['status'] = 'draft';
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
     * 生成唯一 Slug：优先使用用户填写的 slug，其次从标题生成。
     */
    private function makeSlug(?string $slug, string $title, ?int $ignoreId): ?string
    {
        $base = $slug ?: Str::slug($title);

        if (!$base) {
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

        if ($note->cover_image) {
            Storage::disk('public')->delete($note->cover_image);
        }

        $note->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', '文章已删除。');
    }
}
