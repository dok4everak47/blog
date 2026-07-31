<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 全文搜索支持：pg_trgm 扩展 + 标题/正文 trigram GIN 索引。
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX IF NOT EXISTS notes_title_trgm ON notes USING gin (lower(title) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS notes_content_trgm ON notes USING gin (lower(content) gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS notes_content_trgm');
        DB::statement('DROP INDEX IF EXISTS notes_title_trgm');
        DB::statement('DROP EXTENSION IF EXISTS pg_trgm');
    }
};
