<?php

namespace Database\Seeders;

use App\Models\ScraperConfig;
use Illuminate\Database\Seeder;

class ScraperConfigSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key'         => 'keywords',
                'label'       => 'Keywords Predefined',
                'description' => 'Daftar tombol AI topic di halaman Ref Articles',
                'type'        => 'array',
                'value'       => json_encode([
                    'ChatGPT', 'Gemini', 'Claude', 'DeepSeek', 'OpenAI', 'LLM',
                    'AI Agent', 'Machine Learning', 'Artificial Intelligence',
                    'Anthropic', 'Mistral', 'AI Search',
                ]),
            ],
            [
                'key'         => 'min_year',
                'label'       => 'Tahun Minimum Artikel',
                'description' => 'Hanya scrape artikel dari tahun ini ke atas',
                'type'        => 'integer',
                'value'       => '2022',
            ],
            [
                'key'         => 'source_urls',
                'label'       => 'Source Sitemap URLs',
                'description' => 'Domain dan sitemap_index.xml yang di-scrape',
                'type'        => 'array',
                'value'       => json_encode([
                    'searchengineland.com'    => 'https://searchengineland.com/sitemap_index.xml',
                    'searchenginejournal.com' => 'https://www.searchenginejournal.com/sitemap_index.xml',
                ]),
            ],
            [
                'key'         => 'confidence_threshold',
                'label'       => 'Confidence Threshold',
                'description' => 'Minimal score (%) agar artikel diproses',
                'type'        => 'integer',
                'value'       => '55',
            ],
            [
                'key'         => 'publish_schedule_hours',
                'label'       => 'Jadwal Publish',
                'description' => 'Jam-jam publish post AI dalam format array (HH:MM)',
                'type'        => 'array',
                'value'       => json_encode(['08:00', '13:00', '16:00']),
            ],
            [
                'key'         => 'daily_limit',
                'label'       => 'Limit Harian',
                'description' => 'Max artikel yang di-scrape per hari',
                'type'        => 'integer',
                'value'       => '5',
            ],
        ];

        foreach ($defaults as $d) {
            ScraperConfig::updateOrCreate(
                ['key' => $d['key']],
                [
                    'label'       => $d['label'],
                    'description' => $d['description'] ?? null,
                    'type'        => $d['type'],
                    'value'       => $d['value'],
                ]
            );
        }
    }
}
