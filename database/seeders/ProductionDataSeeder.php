<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WebIdentity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionDataSeeder extends Seeder
{
    /**
     * ================================================================
     * PRODUCTION DATA SEEDER
     * ================================================================
     * GUNAKAN SEEDER INI UNTUK SETUP DATA AWAL DI PRODUCTION SERVER.
     *
     * CARA PAKAI:
     * 1. php artisan migrate --seed --class=ProductionDataSeeder
     *    (jika fresh install)
     *
     * 2. Atau import langsung via SQL:
     *    php artisan db:seed --class=ProductionDataSeeder
     *    (jika database sudah ada)
     *
     * ================================================================
     * CREDENTIALS:
     *   Email:    it@kangwendra.com
     *   Password: @R4y4ndr4
     *
     * Ganti credentials setelah login!
     * ================================================================
     */

    public function run(): void
    {
        // --- USERS ---
        // Update atau create admin dengan credentials production
        $admin = User::updateOrCreate(
            ['email' => 'it@kangwendra.com'],
            [
                'name' => 'KangWendra Admin',
                'password' => Hash::make('@R4y4ndr4'),
                'status' => 'active',
                'failed_upload_attempts' => 0,
            ]
        );
        $admin->assignRole('admin');

        // Hapus semua user lain EXCEPT admin ini
        User::where('email', '!=', 'it@kangwendra.com')->delete();

        $this->command->info('[ProductionDataSeeder] Admin user: it@kangwendra.com / @R4y4ndr4');
        $this->command->info('[ProductionDataSeeder] Other users deleted. Remaining: ' . User::count());

        // --- WEB IDENTITY ---
        $existingIdentity = WebIdentity::first();
        if (!$existingIdentity) {
            WebIdentity::create([
                'web_name' => 'Kangwendra',
                'email' => 'it@kangwendra.com',
                'domain' => 'https://kangwendra.com',
                'phone_number' => '#',
                'facebook_link' => '#',
                'instagram_link' => '#',
                'youtube_link' => '#',
                'twitter_link' => '#',
                'google_maps' => 'https://maps.google.com/',
                'meta_title' => 'Kangwendra — Portal Berita AI Indonesia',
                'meta_description' => 'Portal berita terpercaya untuk berita Artificial Intelligence, SEO, Machine Learning, dan teknologi terkini dalam Bahasa Indonesia.',
                'meta_keywords' => 'portal berita AI, artificial intelligence, machine learning, SEO, deep learning, teknologi, chatbot, generative AI, berita teknologi',
                'og_image' => null,
                'favicon' => null,
                'logo' => null,
                'status' => 'active',
                'api_posts' => null,
                'api_key_master' => null,
                'version' => '1.0.0',
                'is_master' => true,
            ]);
            $this->command->info('[ProductionDataSeeder] WebIdentity created.');
        } else {
            // Update existing WebIdentity
            $existingIdentity->update([
                'web_name' => 'Kangwendra',
                'email' => 'it@kangwendra.com',
                'domain' => 'https://kangwendra.com',
                'meta_title' => 'Kangwendra — Portal Berita AI Indonesia',
                'meta_description' => 'Portal berita terpercaya untuk berita Artificial Intelligence, SEO, Machine Learning, dan teknologi terkini dalam Bahasa Indonesia.',
                'meta_keywords' => 'portal berita AI, artificial intelligence, machine learning, SEO, deep learning, teknologi, chatbot, generative AI, berita teknologi',
            ]);
            $this->command->info('[ProductionDataSeeder] WebIdentity updated.');
        }
    }
}
