<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('youtube_id', 32)->nullable()->unique()->after('id');
            $table->timestamp('youtube_published_at')->nullable()->index()->after('link_yt');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropUnique(['youtube_id']);
            $table->dropIndex(['youtube_published_at']);
            $table->dropColumn(['youtube_id', 'youtube_published_at']);
        });
    }
};
