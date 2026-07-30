<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagRelation extends Model
{
    protected $table = 'tag_relations';

    protected $fillable = ['note_id', 'tag_id'];

    public $timestamps = true;

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
