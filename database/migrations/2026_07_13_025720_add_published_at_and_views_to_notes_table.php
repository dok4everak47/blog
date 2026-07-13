<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('status');
            $table->unsignedInteger('views')->default(0)->after('published_at');
            $table->index(['status', 'published_at']);
        });

        // 已发布文章回填 published_at
        \Illuminate\Support\Facades\DB::table('notes')
            ->where('status', 'published')
            ->whereNull('published_at')
            ->update(['published_at' => \Illuminate\Support\Facades\DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
            $table->dropColumn(['published_at', 'views']);
        });
    }
};
