<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Posts;
use App\Services\NewsService;

class UpdateExistingPostsDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-existing-posts-domain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update domain for existing posts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating existing posts domain...');

        $newsService = new NewsService();
        $posts = Posts::whereNull('domain')->orWhere('domain', 'news.google.com')->get();

        $count = 0;
        foreach ($posts as $post) {
            // Hanya ekstrak dari judul, karena deskripsi selalu dapat news.google.com
            $domain = $newsService->extractDomainFromTitle($post->title);

            // Jika domain ditemukan dan bukan news.google.com
            if ($domain && $domain !== 'news.google.com') {
                $post->domain = $domain;
                $post->save();
                $count++;
                $this->info("Updated: {$post->title} -> {$domain}");
            }
        }

        $this->info("Successfully updated {$count} posts!");
        return 0;
    }
}
