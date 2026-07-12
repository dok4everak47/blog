<?php

namespace App\Enums;

enum NoteStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * 人类可读的中文标签
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Published => '已发布',
            self::Archived => '已归档',
        };
    }

    /**
     * 是否对访客可见（前台公开列表）
     */
    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
