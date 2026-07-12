<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 为笔记（文章）增加 发布状态 与 URL Slug 支持。
     */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('status')->default('published')->after('content');
            $table->string('slug')->nullable()->unique()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['status', 'slug']);
        });
    }
};
