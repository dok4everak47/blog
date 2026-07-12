<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 给中间表和名称字段加唯一索引，防止重复数据。
     */
    public function up(): void
    {
        // note_tag 复合唯一索引（防止同一文章重复关联同一标签）
        // 先清理可能存在的重复行，再加索引
        DB::statement('DELETE FROM note_tag WHERE id NOT IN (SELECT MIN(id) FROM note_tag GROUP BY note_id, tag_id)');

        Schema::table('note_tag', function (Blueprint $table) {
            $table->unique(['note_id', 'tag_id'], 'note_tag_note_tag_unique');
        });

        // tags.name 唯一
        Schema::table('tags', function (Blueprint $table) {
            $table->unique('name');
        });

        // categories.name 唯一
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('note_tag', function (Blueprint $table) {
            $table->dropUnique('note_tag_note_tag_unique');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_name_unique');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
        });
    }
};
