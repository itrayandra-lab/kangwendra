<?php

namespace Database\Seeders;

use App\Models\Menus;
use App\Models\ScraperConfig;
use Illuminate\Database\Seeder;

class MenuAndKeywordSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Import menus dari server SQL ───
        $menus = [
            // Parent menus
            ['id' => 1,  'name' => 'Home',                    'slug' => 'https://kangwendra.com/',                  'order' => 1,  'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            ['id' => 10, 'name' => 'AI Fundamental',          'slug' => 'https://kangwendra.com/AI-Fundamental',     'order' => 3,  'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            ['id' => 11, 'name' => 'Reflection',              'slug' => 'https://kangwendra.com/reflection',         'order' => 13, 'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            ['id' => 13, 'name' => 'About',                   'slug' => 'https://kangwendra.com/about',              'order' => 25, 'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            ['id' => 16, 'name' => 'AI Architecture',         'slug' => 'https://kangwendra.com/ai-architecture',    'order' => 2,  'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            ['id' => 17, 'name' => 'AI Branding',             'slug' => 'https://kangwendra.com/ai-branding',        'order' => 4,  'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            ['id' => 21, 'name' => 'Contact',                 'slug' => 'Contact',                                    'order' => 18, 'status' => 'active', 'type_1' => 'parent', 'type_2' => 'page',    'parent_id' => null, 'created_by' => 1],
            ['id' => 28, 'name' => 'AI Marketing',           'slug' => 'https://kangwendra.com/ai-marketing',       'order' => 5,  'status' => 'active', 'type_1' => 'parent', 'type_2' => 'link',    'parent_id' => null, 'created_by' => 1],
            // Submenus -> About (parent_id=13)
            ['id' => 6,  'name' => 'Teh Cahya',              'slug' => 'tehcahya.com',                              'order' => 6,  'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'link',    'parent_id' => 13, 'created_by' => 1],
            ['id' => 8,  'name' => 'tipsbranding.id',        'slug' => 'tipsbranding.id',                           'order' => 8,  'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'link',    'parent_id' => 13, 'created_by' => 1],
            ['id' => 19, 'name' => 'raycorp.id',             'slug' => 'raycorp.id',                                 'order' => 16, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'link',    'parent_id' => 13, 'created_by' => 1],
            ['id' => 20, 'name' => 'rayacademy.id',          'slug' => 'rayacademy.id',                              'order' => 17, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'link',    'parent_id' => 13, 'created_by' => 1],
            // Submenus -> Reflection (parent_id=11)
            ['id' => 15, 'name' => 'Wakeup Indonesia',        'slug' => 'https://kangwendra.com/wakeup-indonesia',   'order' => 9,  'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'link',    'parent_id' => 11, 'created_by' => 1],
            // Submenus -> AI Architecture (parent_id=16)
            ['id' => 22, 'name' => 'AI Selection Framework',   'slug' => 'ai-selection-risk-swbf6',                   'order' => 19, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'page',    'parent_id' => 16, 'created_by' => 1],
            ['id' => 23, 'name' => 'Decision Architecture',    'slug' => 'decision-architecture-iufvy',              'order' => 20, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'page',    'parent_id' => 16, 'created_by' => 1],
            ['id' => 25, 'name' => 'Trust Infrastructure',     'slug' => 'trust-infrastructure-eboqq',                 'order' => 22, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'page',    'parent_id' => 16, 'created_by' => 1],
            ['id' => 26, 'name' => 'Selection Risk & Probability', 'slug' => 'selection-risk-probability-ymquj',  'order' => 23, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'page',    'parent_id' => 16, 'created_by' => 1],
            ['id' => 27, 'name' => 'Generative Discovery',     'slug' => 'generative-discovery-rpjrr',                'order' => 24, 'status' => 'active', 'type_1' => 'submenu', 'type_2' => 'page',    'parent_id' => 16, 'created_by' => 1],
        ];

        foreach ($menus as $menu) {
            Menus::updateOrCreate(
                ['id' => $menu['id']],
                [
                    'name'      => $menu['name'],
                    'slug'      => $menu['slug'],
                    'order'     => $menu['order'],
                    'status'    => $menu['status'],
                    'type_1'    => $menu['type_1'],
                    'type_2'    => $menu['type_2'],
                    'parent_id' => $menu['parent_id'],
                    'created_by' => $menu['created_by'],
                ]
            );
        }

        $this->command->info('Menus imported: ' . count($menus) . ' entries');

        // ─── Update ScraperConfig keywords ───
        ScraperConfig::updateOrCreate(
            ['key' => 'keywords'],
            [
                'label'       => 'Keywords Predefined',
                'description' => 'Daftar tombol AI topic di halaman Ref Articles',
                'type'        => 'array',
                'value'       => json_encode([
                    // Existing keywords
                    'ChatGPT', 'Gemini', 'Claude', 'DeepSeek', 'OpenAI', 'LLM',
                    'AI Agent', 'Machine Learning', 'Artificial Intelligence',
                    'Anthropic', 'Mistral', 'AI Search',
                    // AI Branding
                    'AI Branding', 'Brand Strategy', 'Brand Architecture',
                    'Brand Identity', 'Brand Positioning', 'AI Brand',
                    // AI Fundamental
                    'AI Fundamental', 'AI Basics', 'Artificial General Intelligence',
                    'Neural Network', 'Deep Learning', 'AI Fundamentals',
                    // AI Architecture
                    'AI Architecture', 'AI System Design', 'AI Infrastructure',
                    'AI Selection Risk', 'Decision Architecture',
                    'Trust Infrastructure', 'Generative Discovery',
                    // AI Marketing
                    'AI Marketing', 'AI Content Marketing', 'AI SEO',
                    'Marketing Automation', 'AI Advertising',
                    // Brand & Business
                    'Business Strategy', 'Digital Marketing', 'Technology Trends',
                ]),
            ]
        );

        $this->command->info('ScraperConfig keywords updated');
    }
}
