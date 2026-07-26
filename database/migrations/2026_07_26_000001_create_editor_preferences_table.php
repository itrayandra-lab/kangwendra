<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editor_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 255)->unique();
            $table->string('topic', 255)->nullable();
            $table->integer('approved_count')->default(0);
            $table->integer('rejected_count')->default(0);
            $table->integer('unpublished_count')->default(0);
            $table->decimal('score', 8, 4)->default(0);
            $table->decimal('confidence', 8, 4)->default(50);
            $table->text('blocklist_urls')->nullable();
            $table->text('blocklist_patterns')->nullable();
            $table->timestamps();

            $table->index('keyword');
            $table->index('score');
            $table->index('confidence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editor_preferences');
    }
};
