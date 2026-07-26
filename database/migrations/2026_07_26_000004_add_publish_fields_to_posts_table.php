<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('published_by', 50)->default('system')->after('status');
            $table->timestamp('unpublished_at')->nullable()->after('published_at');
            $table->string('unpublished_reason', 255)->nullable()->after('unpublished_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'published_by',
                'unpublished_at',
                'unpublished_reason',
            ]);
        });
    }
};
