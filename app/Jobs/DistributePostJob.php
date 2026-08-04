<?php

namespace App\Jobs;

use App\Models\PostCategory;
use App\Models\PostTags;
use App\Models\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DistributePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; 
    public $failOnTimeout = true;

    protected $webhookUrl;
    protected $targetDomain;
    protected $apiKey;
    protected $metaData;

    public function __construct($webhookUrl, $targetDomain, $apiKey, $metaData)
    {
        $this->webhookUrl = $webhookUrl;
        $this->targetDomain = $targetDomain;
        $this->apiKey = $apiKey; 
        $this->metaData = $metaData;
    }

    public function handle()
    {
        ini_set('memory_limit', '256M'); // Cukup untuk distribusi webhook
        Log::info("Job Started: Processing for domain {$this->targetDomain}", [
            'session_id' => $this->metaData['session_id']
        ]);

        $n8nPayload = [
            'session_id' => $this->metaData['session_id'],
            'title' => $this->metaData['original_title'],
            'content' => $this->metaData['original_content'],
        ];

        $response = Http::timeout(300)->post($this->webhookUrl, $n8nPayload);

        if ($response->failed()) {
            Log::error("n8n Connection Failed", [
                'domain' => $this->targetDomain,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception("Failed to connect to n8n for {$this->targetDomain}");
        }

        $body = $response->json();

        if (!isset($body['wordpress_html'])) {
            Log::error("Invalid n8n response structure (missing wordpress_html)", ['response' => $body]);
            throw new \Exception("Invalid n8n response structure");
        }

        $finalTitle = $body['headline'] ?? $this->metaData['original_title'];
        $finalContent = $body['wordpress_html'];
        $metaTitle = $body['seo']['meta_title'] ?? $finalTitle;
        $metaDesc = $body['seo']['meta_description'] ?? Str::limit(strip_tags($finalContent), 160);
        $slug = $body['seo']['slug'] ?? Str::slug($finalTitle);

        Log::info("n8n Response Received. Sending to target domain.", [
            'final_title' => $finalTitle,
            'domain' => $this->targetDomain
        ]);

        $tagIds = $this->metaData['tags'] ?? [];
        if (is_string($tagIds)) {
            $tagIds = json_decode($tagIds, true) ?? [];
        }

        $resolvedTags = [];
        if (!empty($tagIds)) {
            $resolvedTags = PostTags::whereIn('id', $tagIds)
                ->get(['id', 'name'])
                ->toArray();
        }

        $categoryData = null;
        $categoryName = $this->metaData['category'] ?? null;
        
        if ($categoryName) {
            $category = PostCategory::where('name', $categoryName)->first();
            $categoryData = $category 
                ? ['id' => $category->id, 'name' => $category->name]
                : ['id' => null, 'name' => $categoryName];
        }

        $finalPayload = [
            'title'        => $finalTitle,
            'slug'         => $slug,
            'status'       => 'inactive',
            'content'      => $finalContent,
            'excerpt'      => $metaDesc, 
            'image'        => $this->metaData['image'],
            'tags'         => $resolvedTags,
            'category'     => $categoryData,
            'published_at' => $this->metaData['published_at'],
            'meta_data'    => [
                'seo_title'   => $metaTitle,
                'seo_desc'    => $metaDesc,
                'yoast'       => $body['yoast'] ?? null,
                'rank_math'   => $body['rank_math'] ?? null,
                'schema'      => $body['schema'] ?? null,
                'open_graph'  => $body['open_graph'] ?? null,
                'stats'       => $body['stats'] ?? null,
            ]
        ];

        $targetResponse = Http::timeout(60)
            ->withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json'
            ])
            ->post("https://{$this->targetDomain}/api/posts", $finalPayload);

        if ($targetResponse->failed()) {
            Log::error("Failed to post to target domain API", [
                'domain' => $this->targetDomain,
                'status' => $targetResponse->status(),
                'response' => $targetResponse->body()
            ]);
            throw new \Exception("Failed to post to target domain: {$this->targetDomain}");
        }

        Log::info("Job Completed Successfully for {$this->targetDomain}");
    }
}