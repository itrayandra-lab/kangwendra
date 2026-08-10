<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application database.
     */
    public function run(): void
    {
        // Safety: Jika APP_ENV=production, jangan auto-seed user test
        if (env('APP_ENV') === 'production') {
            $this->command->warn('APP_ENV=production detected. Skipping DatabaseSeeder.');
            $this->command->warn('Untuk setup production data, jalankan: php artisan db:seed --class=ProductionDataSeeder');
            return;
        }

        $this->call([
            UserSeeder::class,
            PostCategorySeeder::class,
            PostTagSeeder::class,
            PostSeeder::class,
            PublishScheduleSeeder::class,
            ScraperConfigSeeder::class,
            MenuAndKeywordSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
}
