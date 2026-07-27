<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraper_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. 'keywords', 'min_year', 'source_urls', 'confidence_threshold', 'daily_limit'
            $table->text('value');             // JSON string for array values, string for scalars
            $table->string('type')->default('string'); // 'array' or 'string' or 'integer'
            $table->string('label');           // Human-readable label
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraper_configs');
    }
};
