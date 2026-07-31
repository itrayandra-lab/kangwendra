<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ─── AI News Portal Categories ───
            ['name' => 'AI & Teknologi',  'slug' => 'ai-teknologi'],
            ['name' => 'AI Marketing',   'slug' => 'ai-marketing'],
            ['name' => 'AI Fundamental',  'slug' => 'ai-fundamental'],
            ['name' => 'AI Architecture', 'slug' => 'ai-architecture'],
            ['name' => 'Brand Strategy',  'slug' => 'brand-strategy'],
            ['name' => 'Reflections',     'slug' => 'reflections'],
            // ─── General Categories ───
            ['name' => 'Bisnis',          'slug' => 'bisnis'],
            ['name' => 'Otomotif',        'slug' => 'otomotif'],
            ['name' => 'Gaya Hidup',      'slug' => 'gaya-hidup'],
            ['name' => 'Kesehatan',       'slug' => 'kesehatan'],
            ['name' => 'Keuangan',        'slug' => 'keuangan'],
            ['name' => 'Gaming',          'slug' => 'gaming'],
            ['name' => 'Sains',           'slug' => 'sains'],
            ['name' => 'Pendidikan',      'slug' => 'pendidikan'],
            ['name' => 'Hiburan',         'slug' => 'hiburan'],
            ['name' => 'Technology',      'slug' => 'technology'],
            ['name' => 'Lifestyle',       'slug' => 'lifestyle'],
            ['name' => 'Programming',     'slug' => 'programming'],
            ['name' => 'News',            'slug' => 'news'],
            ['name' => 'Tutorials',       'slug' => 'tutorials'],
        ];

        foreach ($categories as $category) {
            PostCategory::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }
    }
}
