<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * 笔记封面图的一体化操作：上传新图 / 替换 / 移除。
 *
 * 封面图与缩略图总是成对出现，删除时也要成对删除。
 * 此 Service 把这对约定收拢到一处，消除 Controller 中 5 处手动删除的双 if 块。
 */
class CoverImageService
{
    public function __construct(
        private readonly ImageProcessor $images,
    ) {}

    /**
     * 上传新封面（不带旧图清理，用于首次创建）。
     *
     * @return array{cover: string|null, thumbnail: string|null}
     */
    public function upload(UploadedFile $file): array
    {
        $cover = $file->store('covers', 'public');
        $thumbnail = $this->images->thumbnail($cover);

        return ['cover' => $cover, 'thumbnail' => $thumbnail];
    }

    /**
     * 用新上传的封面替换旧封面（含缩略图清理）。
     *
     * @return array{cover: string, thumbnail: string|null}
     */
    public function replace(?string $oldCover, ?string $oldThumbnail, UploadedFile $file): array
    {
        $this->delete($oldCover, $oldThumbnail);

        return $this->upload($file);
    }

    /**
     * 移除封面与缩略图（同时清理磁盘文件）。
     *
     * @return array{cover: null, thumbnail: null}
     */
    public function remove(?string $oldCover, ?string $oldThumbnail): array
    {
        $this->delete($oldCover, $oldThumbnail);

        return ['cover' => null, 'thumbnail' => null];
    }

    /**
     * 根据请求意图决定走哪条路径（无操作 / 上传 / 移除）。
     *
     * 当 Controller 既要处理"上传新图"又要处理"勾选移除"时使用。
     *
     * @return array{cover: string|null, thumbnail: string|null}|null
     *                                                                null 表示无操作（未上传也未勾选移除），调用方据此跳过更新
     */
    public function apply(?string $oldCover, ?string $oldThumbnail, ?UploadedFile $newFile, bool $remove): ?array
    {
        if ($newFile) {
            return $this->replace($oldCover, $oldThumbnail, $newFile);
        }

        if ($remove) {
            return $this->remove($oldCover, $oldThumbnail);
        }

        return null;
    }

    private function delete(?string $cover, ?string $thumbnail): void
    {
        if ($cover) {
            Storage::disk('public')->delete($cover);
        }
        if ($thumbnail) {
            // thumbnail_url 列存的是相对路径（如 'thumbnails/xxx_thumb.jpg'）
            Storage::disk('public')->delete($thumbnail);
        }
    }
}
