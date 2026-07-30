<?php

namespace App\Models;

use App\Enums\NoteStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'category_id', 'user_id', 'status', 'slug', 'cover_image', 'thumbnail_url', 'excerpt', 'published_at', 'views'];

    protected function casts(): array
    {
        return [
            'status' => NoteStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * 状态快捷判断
     */
    public function isDraft(): bool
    {
        return $this->status === NoteStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === NoteStatus::Published;
    }

    /**
     * 仅查询已发布文章（前台公开访问用）
     * status = published 且 published_at <= 当前时间（支持定时发布）
     */
    public function scopePublished($query)
    {
        return $query->where('status', NoteStatus::Published->value)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    /**
     * 仅查询草稿（后台管理用）
     */
    public function scopeDraft($query)
    {
        return $query->where('status', NoteStatus::Draft->value);
    }

    /**
     * 仅查询指定用户的文章（后台安全查询，防止跨用户数据泄露）
     */
    public function scopeForUser($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        return $query->where('user_id', $user?->id ?? 0);
    }

    /**
     * 预计阅读时间（分钟），中文按 ~400 字/分钟估算
     */
    public function readingMinutes(): int
    {
        $chars = mb_strlen(strip_tags($this->content ?? ''));

        return max(1, (int) round($chars / 400));
    }

    /**
     * 从 content 自动生成摘要（去除 Markdown 语法后截取）
     */
    public static function generateExcerpt(?string $content, int $limit = 200): string
    {
        if (empty($content)) {
            return '';
        }

        $text = preg_replace('/!\[.*?\]\([^)]+\)|\[.*?\]\([^)]+\)|`[^`]+`/', '', $content);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', trim($text));

        return Str::limit($text, $limit);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 作者（多作者/权限隔离的基础）。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->oldest();
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * 基于共同标签获取相关文章
     */
    public function related(int $limit = 5): Collection
    {
        $tagIds = $this->tags()->pluck('tags.id');

        if ($tagIds->isEmpty()) {
            return collect();
        }

        return static::published()
            ->where('id', '!=', $this->id)
            ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
            ->withCount(['tags as shared_tags_count' => fn ($q) => $q->whereIn('tags.id', $tagIds)])
            ->with('category')
            ->orderByDesc('shared_tags_count')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 封面图公开访问地址（存储于 storage/app/public/covers）。
     * 返回根相对路径（/storage/...），兼容开发端口与正式部署。
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image
            ? '/storage/'.ltrim($this->cover_image, '/')
            : null;
    }

    /**
     * 缩略图公开访问地址（400px 宽，存储于 storage/app/public/thumbnails）。
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $raw = $this->attributes['thumbnail_url'] ?? null;

        return $raw
            ? '/storage/'.ltrim($raw, '/')
            : null;
    }
}
