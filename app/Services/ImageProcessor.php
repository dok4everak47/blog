<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * 图片处理基础设施：上传校验 + 缩略图生成。
 *
 * 把原本散落在 Controller / Model 中的图片逻辑集中到此，
 * 既消除重复代码，又让 Note 模型回归纯数据职责。
 */
class ImageProcessor
{
    /**
     * 校验并存储单张图片到 public disk。
     *
     * @param  array  $input  原始输入（含 image 字段）
     * @param  string  $directory  存储目录，如 'covers'、'uploads'
     * @param  int  $maxKb  最大体积（KB）
     * @param  string[]  $messages  自定义校验消息（可选）
     * @return array{path: string, error?: MessageBag}
     */
    public function upload(array $input, string $directory, int $maxKb = 10240, array $messages = []): array
    {
        $validator = Validator::make($input, [
            'image' => "required|image|mimes:jpeg,png,jpg,webp,gif|max:{$maxKb}",
        ], $messages);

        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        return ['path' => UploadedFile::createFromBase($input['image'])->store($directory, 'public')];
    }

    /**
     * 生成指定宽度的缩略图（保持宽高比），存入 thumbnails 目录。
     *
     * @param  string  $coverPath  storage/app/public/<coverPath> 形式的相对路径
     * @param  int  $maxWidth  目标宽度（像素）
     * @return string|null 缩略图相对路径；失败/null 表示原图已够小或不支持
     */
    public function thumbnail(string $coverPath, int $maxWidth = 400): ?string
    {
        $fullPath = Storage::disk('public')->path($coverPath);

        if (! file_exists($fullPath)) {
            return null;
        }

        $info = getimagesize($fullPath);
        if (! $info) {
            return null;
        }

        [$origWidth, $origHeight, $imageType] = $info;

        // 原图已够小，无需生成
        if ($origWidth <= $maxWidth) {
            return null;
        }

        $ratio = $maxWidth / $origWidth;
        $newWidth = $maxWidth;
        $newHeight = (int) round($origHeight * $ratio);

        $mime = image_type_to_mime_type($imageType);
        $src = $this->createSourceImage($mime, $fullPath);
        if (! $src) {
            return null;
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        $this->preserveTransparency($mime, $src, $dst);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        $pathInfo = pathinfo($coverPath);
        $thumbPath = 'thumbnails/'.$pathInfo['filename'].'_thumb.'.$pathInfo['extension'];
        $outputPath = Storage::disk('public')->path($thumbPath);

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->writeOutputImage($mime, $dst, $outputPath);

        imagedestroy($src);
        imagedestroy($dst);

        return file_exists($outputPath) ? $thumbPath : null;
    }

    private function createSourceImage(string $mime, string $path)
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };
    }

    private function preserveTransparency(string $mime, $src, $dst): void
    {
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);

            return;
        }

        if ($mime === 'image/gif') {
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
    }

    private function writeOutputImage(string $mime, $dst, string $path): void
    {
        match ($mime) {
            'image/jpeg' => imagejpeg($dst, $path, 85),
            'image/png' => imagepng($dst, $path, 9),
            'image/gif' => imagegif($dst, $path),
            'image/webp' => imagewebp($dst, $path, 85),
            default => null,
        };
    }
}
