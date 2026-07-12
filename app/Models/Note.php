<?php

namespace App\Models;

use App\Enums\NoteStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'category_id', 'status', 'slug', 'cover_image'];

    protected function casts(): array
    {
        return [
            'status' => NoteStatus::class,
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
     */
    public function scopePublished($query)
    {
        return $query->where('status', NoteStatus::Published->value);
    }

    /**
     * 仅查询草稿（后台管理用）
     */
    public function scopeDraft($query)
    {
        return $query->where('status', NoteStatus::Draft->value);
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
     * 作者（多作者/权限隔离的基础）。
     * 注意：user_id 不进入 $fillable，只能由控制器通过
     * auth()->user()->notes()->create() 赋值，杜绝表单伪造归属。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
