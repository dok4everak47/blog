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

    protected $fillable = ['title', 'content', 'category_id', 'user_id', 'status', 'slug', 'cover_image', 'thumbnail_url', 'excerpt'];

    protected $with = ['category', 'user'];

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
        if (empty($content)) return '';

        $text = preg_replace('/!\[.*?\]\([^)]+\)|\[.*?\]\([^)]+\)|`[^`]+`/', '', $content);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', trim($text));

        return \Illuminate\Support\Str::limit($text, $limit);
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

    /**
     * 缩略图公开访问地址（400px 宽，存储于 storage/app/public/thumbnails）。
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $raw = $this->attributes['thumbnail_url'] ?? null;
        return $raw
            ? '/storage/' . ltrim($raw, '/')
            : null;
    }

    /**
     * 从封面图生成 400px 宽缩略图（保持宽高比），存入 thumbnails 目录。
     *
     * @param string $coverPath storage/app/public/covers/xxx.jpg 形式的相对路径
     * @return string|null 缩略图的存储相对路径，失败返回 null
     */
    public static function generateThumbnail(string $coverPath): ?string
    {
        $fullPath = Storage::disk('public')->path($coverPath);

        if (!file_exists($fullPath)) {
            return null;
        }

        // 获取原图信息
        $info = getimagesize($fullPath);
        if (!$info) {
            return null;
        }

        [$origWidth, $origHeight, $imageType] = $info;

        // 目标宽度 400px，按比例算高度
        $maxWidth = 400;
        if ($origWidth <= $maxWidth) {
            return null; // 原图已够小，不生成
        }

        $ratio = $maxWidth / $origWidth;
        $newWidth = $maxWidth;
        $newHeight = (int) round($origHeight * $ratio);

        // 根据 MIME 类型创建 GD 图像
        $mime = image_type_to_mime_type($imageType);
        switch ($mime) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($fullPath);
                break;
            case 'image/png':
                $src = imagecreatefrompng($fullPath);
                break;
            case 'image/gif':
                $src = imagecreatefromgif($fullPath);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($fullPath);
                break;
            default:
                return null; // 不支持的格式
        }

        if (!$src) {
            return null;
        }

        // 创建目标画布
        $dst = imagecreatetruecolor($newWidth, $newHeight);

        // PNG 透明度保留
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        // GIF 透明度保留
        elseif ($mime === 'image/gif') {
            $transparentColor = imagecolortransparent($src);
            if ($transparentColor >= 0) {
                $transparentIndex = imagecolorallocatealpha(
                    $dst,
                    ($transparentColor >> 16) & 0xFF,
                    ($transparentColor >> 8) & 0xFF,
                    $transparentColor & 0xFF,
                    127
                );
                imagefill($dst, 0, 0, $transparentIndex);
                imagecolortransparent($dst, $transparentIndex);
            }
        }

        // 高质量缩放
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // 生成文件名：原文件名 + _thumb + 扩展名
        $pathInfo = pathinfo($coverPath);
        $thumbFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        $thumbPath = 'thumbnails/' . $thumbFilename;

        // 保存到磁盘
        $outputFullPath = Storage::disk('public')->path($thumbPath);
        $dir = dirname($outputFullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($dst, $outputFullPath, 85);
                break;
            case 'image/png':
                imagepng($dst, $outputFullPath, 9);
                break;
            case 'image/gif':
                imagegif($dst, $outputFullPath);
                break;
            case 'image/webp':
                imagewebp($dst, $outputFullPath, 85);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);

        return file_exists($outputFullPath) ? $thumbPath : null;
    }
}
