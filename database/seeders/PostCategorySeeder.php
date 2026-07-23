<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Teknologi',      'slug' => 'teknologi',      'description' => 'Berita Teknologi, AI, dan Gadget terbaru'],
            ['name' => 'Bisnis',         'slug' => 'bisnis',         'description' => 'Berita Bisnis dan Ekonomi digital'],
            ['name' => 'Otomotif',       'slug' => 'otomotif',       'description' => 'Berita Otomotif, Mobil Listrik, dan Motor'],
            ['name' => 'Gaya Hidup',     'slug' => 'gaya-hidup',     'description' => 'Fashion, Beauty, dan Gaya Hidup'],
            ['name' => 'Kesehatan',      'slug' => 'kesehatan',      'description' => 'Kesehatan, Wellness, dan Nutrisi'],
            ['name' => 'Keuangan',       'slug' => 'keuangan',       'description' => 'Crypto, Investasi, dan Keuangan'],
            ['name' => 'Gaming',         'slug' => 'gaming',        'description' => 'Game, Esports, dan Gaming'],
            ['name' => 'Sains',          'slug' => 'sains',         'description' => 'Sains, Antariksa, dan Teknologi Masa Depan'],
            ['name' => 'Pendidikan',     'slug' => 'pendidikan',    'description' => 'Edukasi, Kursus, dan Pengembangan Skill'],
            ['name' => 'Hiburan',        'slug' => 'hiburan',       'description' => 'Streaming, Social Media, dan Hiburan'],
            // Legacy (English) categories - keep for compatibility
            ['name' => 'Technology',     'slug' => 'technology',    'description' => 'Technology News'],
            ['name' => 'Lifestyle',      'slug' => 'lifestyle',     'description' => 'Lifestyle News'],
            ['name' => 'Programming',    'slug' => 'programming',   'description' => 'Programming and Coding'],
            ['name' => 'News',           'slug' => 'news',          'description' => 'General News'],
            ['name' => 'Tutorials',      'slug' => 'tutorials',     'description' => 'How-to and Tutorials'],
        ];

        foreach ($categories as $category) {
            PostCategory::firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name'        => $category['name'],
                    'description' => $category['description'] ?? "Berita {$category['name']}",
                ]
            );
        }
    }
}
