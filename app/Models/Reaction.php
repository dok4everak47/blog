<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $fillable = ['user_id', 'note_id', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /** 当前用户是否已点赞 */
    public static function isLikedBy(int $noteId, ?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return static::where('note_id', $noteId)
            ->where('user_id', $userId)
            ->where('type', 'like')
            ->exists();
    }

    /** 获取文章点赞数 */
    public static function countByNote(int $noteId): int
    {
        return static::where('note_id', $noteId)
            ->where('type', 'like')
            ->count();
    }
}
