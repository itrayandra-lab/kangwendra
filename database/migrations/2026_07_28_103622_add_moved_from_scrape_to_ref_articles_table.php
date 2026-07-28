<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_articles', function (Blueprint $table) {
            $table->boolean('moved_from_scrape')->default(false)->after('ai_research_status');
        });
    }

    public function down(): void
    {
        Schema::table('ref_articles', function (Blueprint $table) {
            $table->dropColumn('moved_from_scrape');
        });
    }
};
