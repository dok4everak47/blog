<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Note extends Model
{
    //
    use HasFactory;
    protected $fillable = ['title', 'content', 'category_id', 'status', 'slug', 'cover_image'];

    /**
     * 草稿 / 已发布 状态判断
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * 仅查询已发布文章（前台公开访问用）
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * 仅查询草稿
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * 预计阅读时间（分钟），中文按 ~400 字/分钟估算
     */
    public function readingMinutes(): int
    {
        $chars = mb_strlen(strip_tags($this->content ?? ''));
        return max(1, (int) round($chars / 400));
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 封面图公开访问地址（存储于 storage/app/public/covers）。
     * 返回根相对路径（/storage/...），兼容开发端口与正式部署。
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image
            ? '/storage/' . ltrim($this->cover_image, '/')
            : null;
    }
}

