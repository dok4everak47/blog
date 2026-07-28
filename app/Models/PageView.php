<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['note_id', 'ip', 'user_agent', 'referer', 'referer_domain'];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
