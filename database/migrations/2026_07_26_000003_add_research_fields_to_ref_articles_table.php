<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_articles', function (Blueprint $table) {
            $table->enum('ai_research_status', ['idle', 'researching', 'done', 'failed'])
                ->default('idle')
                ->after('ai_status');
            $table->string('source_keyword', 255)->nullable()->after('source_domain');
            $table->text('research_notes')->nullable()->after('ai_research_status');
        });
    }

    public function down(): void
    {
        Schema::table('ref_articles', function (Blueprint $table) {
            $table->dropColumn([
                'ai_research_status',
                'source_keyword',
                'research_notes',
            ]);
        });
    }
};
