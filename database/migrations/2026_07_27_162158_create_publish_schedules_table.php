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
        Schema::create('publish_schedules', function (Blueprint $table) {
            $table->id();
            $table->time('time');
            $table->tinyInteger('day_of_week')->nullable(); // 0=Sunday to 6=Saturday, null=every day
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('max_posts')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publish_schedules');
    }
};
