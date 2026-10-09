<?php

namespace App\Services;

use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class YouTubeChannelSyncService
{
    public function sync(int $limit = 15): array
    {
        $channelId = config('services.youtube.kang_wendra_channel_id');
        $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
        $response = Http::retry(3, 500)->timeout(20)->get($feedUrl);

        if (! $response->successful()) {
            throw new RuntimeException("YouTube feed gagal diambil ({$response->status()}).");
        }

        $ownerId = User::query()->value('id');
        if (! $ownerId) {
            throw new RuntimeException('Sinkronisasi YouTube membutuhkan minimal satu user sebagai pemilik video.');
        }

        $items = array_slice($this->parseFeed($response->body()), 0, max(1, $limit));
        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $image = $this->downloadThumbnail($item['youtube_id'], $item['thumbnail_url']);
            $video = Video::query()->where('youtube_id', $item['youtube_id'])
                ->orWhere('link_yt', $item['url'])
                ->first();

            $data = [
                'youtube_id' => $item['youtube_id'],
                'title' => $item['title'],
                'slug' => Str::limit(Str::slug($item['title']).'-'.$item['youtube_id'], 250, ''),
                'link_yt' => $item['url'],
                'description' => $item['description'],
                'image' => $image,
                'youtube_published_at' => Carbon::parse($item['published_at']),
                'created_by' => $ownerId,
            ];

            if ($video) {
                $video->update($data);
                $updated++;
            } else {
                Video::create($data);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated, 'total' => count($items)];
    }

    public function parseFeed(string $xml): array
    {
        $feed = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA);
        if (! $feed) {
            throw new RuntimeException('Respons YouTube bukan XML feed yang valid.');
        }

        $items = [];
        foreach ($feed->entry as $entry) {
            $youtube = $entry->children('http://www.youtube.com/xml/schemas/2015');
            $media = $entry->children('http://search.yahoo.com/mrss/')->group;
            $videoId = trim((string) $youtube->videoId);
            if ($videoId === '') continue;

            $items[] = [
                'youtube_id' => $videoId,
                'title' => trim((string) $entry->title),
                'url' => 'https://www.youtube.com/watch?v='.$videoId,
                'thumbnail_url' => (string) $media->thumbnail->attributes()->url,
                'description' => trim((string) $media->description),
                'published_at' => trim((string) $entry->published),
            ];
        }

        return $items;
    }

    private function downloadThumbnail(string $videoId, string $url): string
    {
        $relativePath = "assets/app/videos/youtube/{$videoId}.jpg";
        $absolutePath = public_path($relativePath);
        if (File::exists($absolutePath)) return $relativePath;

        $response = Http::retry(3, 400)->timeout(20)->get($url);
        if (! $response->successful() || ! str_starts_with($response->header('Content-Type'), 'image/')) {
            throw new RuntimeException("Thumbnail YouTube {$videoId} gagal diunduh.");
        }

        File::ensureDirectoryExists(dirname($absolutePath));
        File::put($absolutePath, $response->body());

        return $relativePath;
    }
}
