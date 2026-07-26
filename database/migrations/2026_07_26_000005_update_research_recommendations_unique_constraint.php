<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old unique index on url (Laravel names it research_recommendations_url_unique)
        Schema::table('research_recommendations', function (Blueprint $table) {
            try {
                DB::statement('ALTER TABLE research_recommendations DROP INDEX research_recommendations_url_unique');
            } catch (\Exception $e) {
                // Index might not exist (already dropped or different name) — ignore
            }
        });

        // Add composite unique on (keyword, url)
        Schema::table('research_recommendations', function (Blueprint $table) {
            $table->unique(['keyword', 'url']);
        });
    }

    public function down(): void
    {
        Schema::table('research_recommendations', function (Blueprint $table) {
            // Drop composite unique
            try {
                DB::statement('ALTER TABLE research_recommendations DROP INDEX research_recommendations_keyword_url_unique');
            } catch (\Exception $e) {
                // ignore
            }
            // Restore single-column unique on url
            $table->unique('url');
        });
    }
};
