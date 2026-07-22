<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_articles', function (Blueprint $table) {
            $table->id();
            $table->string('source_url')->unique();          // URL artikel asli
            $table->string('source_domain');                 // domain sumber, misal: tech.yahoo.com
            $table->string('title');                         // judul artikel asli
            $table->longText('content')->nullable();         // isi artikel asli
            $table->string('image_url')->nullable();         // gambar artikel asli
            $table->json('tags')->nullable();                // tags dari artikel asli
            $table->string('author')->nullable();            // penulis asli
            $table->timestamp('published_at')->nullable();   // tanggal publikasi asli

            // Status pipeline AI
            // pending   = baru di-scrape, belum diproses AI
            // processing = sedang diproses AI
            // done      = sudah dibuatkan artikel baru
            // failed    = gagal diproses AI
            $table->enum('ai_status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->text('ai_error')->nullable();            // pesan error jika gagal
            $table->unsignedBigInteger('generated_post_id')->nullable(); // relasi ke posts.id hasil generate

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_articles');
    }
};
