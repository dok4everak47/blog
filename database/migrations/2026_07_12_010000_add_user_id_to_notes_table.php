<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 给文章加上 user_id，建立「作者」归属感。
     * 这是根除 IDOR 越权（任意登录用户可改删他人文章）的结构性前提。
     * 回填：单作者博客阶段，现有文章统一归属到首个用户。
     */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // 回填历史数据：没有作者的文章归属到首个用户
        if ($firstUserId = DB::table('users')->orderBy('id')->value('id')) {
            DB::table('notes')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
