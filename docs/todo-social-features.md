# 社交功能：文章点赞 + 分享按钮

## 需求

文章详情页增加点赞（Like）和分享按钮。

## 实现步骤

### 1. 数据库迁移

创建 `reactions` 表（支持后续扩展多种 reaction 类型）：

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint PK | |
| user_id | FK → users | 点赞用户 |
| note_id | FK → notes | 被点赞文章 |
| type | string(20) | 默认 `like`，预留未来扩展 |
| created_at / updated_at | timestamps | |
| **唯一索引** | (user_id, note_id, type) | 一人一篇文章只能点一次 |

文件：`database/migrations/2026_07_28_000000_create_reactions_table.php`

### 2. Model: `App\Models\Reaction`

- `$fillable`: user_id, note_id, type
- 关联: `belongsTo(User)`, `belongsTo(Note)`
- 静态方法: `isLikedBy(noteId, userId): bool`、`countByNote(noteId): int`

### 3. 给 `App\Models\Note` 加关联

```php
public function reactions(): HasMany
{
    return $this->hasMany(Reaction::class);
}
```

### 4. Controller: `App\Http\Controllers\ReactionController`

**`toggle(Request, Note)`** — 切换点赞（已赞→取消，未赞→点赞）

- 草稿返回 404
- 需登录（`auth` 中间件）
- 返回 JSON: `{ liked: bool, count: int }`

### 5. 路由

在 `routes/web.php` 的 `auth` 组内加：

```php
Route::post('/notes/{note}/reactions', [ReactionController::class, 'toggle'])
    ->middleware('throttle:30,1')
    ->name('notes.reactions.toggle');
```

### 6. 文章详情页视图

在 `resources/views/notes/show.blade.php`（或对应的样式文件）增加：

**点赞按钮**（Alpine.js，无刷新）：

```blade
<div x-data="{
    liked: {{ Auth::check() && \App\Models\Reaction::isLikedBy($note->id, Auth::id()) ? 'true' : 'false' }},
    count: {{ \App\Models\Reaction::countByNote($note->id) }},
    toggle() {
        fetch('{{ route('notes.reactions.toggle', $note) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => { this.liked = d.liked; this.count = d.count; });
    }
}">
    <button @click="toggle" :class="liked ? 'text-red-500' : 'text-gray-400'">
        <span x-text="liked ? '❤️' : '🤍'"></span>
        <span x-text="count"></span>
    </button>
</div>
```

**分享按钮**（纯 Alpine.js，无需后端）：

```blade
<div x-data="{ show: false }" class="relative">
    <button @click="show = !show">📤 分享</button>
    <div x-show="show" @click.outside="show = false" class="absolute ...">
        <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent('{{ $note->title }}') + '&url=' + encodeURIComponent(window.location.href)" target="_blank">Twitter</a>
        <button @click="navigator.clipboard.writeText(window.location.href); show = false;">复制链接</button>
    </div>
</div>
```

### 7. 更新 `PROJECT_CONTEXT.md`

在数据模型部分加上 `Reaction` 模型。

## 注意事项

- `reactions` 表有唯一索引 `(user_id, note_id, type)`，防止重复点赞
- 点赞用 `fetch` + `X-CSRF-TOKEN`，不需要页面刷新
- 草稿返回 404，与评论行为一致
- 未登录用户看不到点赞按钮（已由 `auth` 中间件保护）
- 分享按钮完全在前端实现，不需要后端接口
