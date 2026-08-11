<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }

    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!$this->hasIndex('posts', 'posts_status_published_at_index')) {
                $table->index(['status', 'published_at']);
            }
            if (!$this->hasIndex('posts', 'posts_published_by_created_at_index')) {
                $table->index(['published_by', 'created_at']);
            }
            if (!$this->hasIndex('posts', 'posts_created_at_index')) {
                $table->index('created_at');
            }
        });

        Schema::table('ref_articles', function (Blueprint $table) {
            if (!$this->hasIndex('ref_articles', 'ref_articles_ai_research_status_index')) {
                $table->index('ai_research_status');
            }
        });

        Schema::table('editor_preferences', function (Blueprint $table) {
            if (!$this->hasIndex('editor_preferences', 'editor_preferences_confidence_index')) {
                $table->index('confidence');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if ($this->hasIndex('posts', 'posts_status_published_at_index')) {
                $table->dropIndex(['status', 'published_at']);
            }
            if ($this->hasIndex('posts', 'posts_published_by_created_at_index')) {
                $table->dropIndex(['published_by', 'created_at']);
            }
            if ($this->hasIndex('posts', 'posts_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });

        Schema::table('ref_articles', function (Blueprint $table) {
            if ($this->hasIndex('ref_articles', 'ref_articles_ai_research_status_index')) {
                $table->dropIndex(['ai_research_status']);
            }
        });

        Schema::table('editor_preferences', function (Blueprint $table) {
            if ($this->hasIndex('editor_preferences', 'editor_preferences_confidence_index')) {
                $table->dropIndex(['confidence']);
            }
        });
    }
};
