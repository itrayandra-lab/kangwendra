<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 255);
            $table->string('url', 500);
            $table->string('title', 500)->nullable();
            $table->string('domain', 255)->nullable();
            $table->string('snippet', 1000)->nullable();
            $table->decimal('confidence_score', 8, 4)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'scraped'])->default('pending');
            $table->string('ref_article_id')->nullable();
            $table->timestamps();

            $table->index('keyword');
            $table->index('status');
            $table->unique('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_recommendations');
    }
};
