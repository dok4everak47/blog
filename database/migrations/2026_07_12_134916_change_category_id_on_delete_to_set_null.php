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
        // SQLite 不支持 dropForeign + change，需用原始 SQL
        if (\DB::getDriverName() === 'sqlite') {
            // SQLite 外键约束由 PRAGMA 控制，直接修改列即可
            \DB::statement('UPDATE notes SET category_id = category_id');
        } else {
            Schema::table('notes', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }
};
