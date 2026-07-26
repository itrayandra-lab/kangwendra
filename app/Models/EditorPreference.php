<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorPreference extends Model
{
    protected $fillable = [
        'keyword',
        'topic',
        'approved_count',
        'rejected_count',
        'unpublished_count',
        'score',
        'confidence',
        'blocklist_urls',
        'blocklist_patterns',
    ];

    protected $casts = [
        'approved_count'    => 'integer',
        'rejected_count'   => 'integer',
        'unpublished_count'=> 'integer',
        'score'            => 'float',
        'confidence'        => 'float',
    ];

    // ── Scopes ──

    public function scopeTopKeywords($query, int $limit = 5)
    {
        return $query->orderByDesc('confidence')->limit($limit);
    }

    public function scopeHasConfidence($query, float $minConfidence = 85)
    {
        return $query->where('confidence', '>=', $minConfidence);
    }

    // ── Helpers ──

    public function addToBlocklist(string $url): void
    {
        $blocklist = $this->getBlocklistUrls();
        if (!in_array($url, $blocklist)) {
            $blocklist[] = $url;
            $this->blocklist_urls = json_encode(array_unique($blocklist));
            $this->save();
        }
    }

    public function isBlocked(string $url): bool
    {
        $blocklist = $this->getBlocklistUrls();
        return in_array($url, $blocklist);
    }

    public function getBlocklistUrls(): array
    {
        if (empty($this->blocklist_urls)) {
            return [];
        }
        $decoded = json_decode($this->blocklist_urls, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function recordApproval(): void
    {
        $this->increment('approved_count');
        $this->increment('confidence', 5);
        $this->score = $this->approved_count - ($this->rejected_count * 2);
        $this->save();
    }

    public function recordRejection(?string $topic = null): void
    {
        if ($topic) {
            $this->increment('rejected_count');
            $this->decrement('confidence', 3);
        }
        $this->score = $this->approved_count - ($this->rejected_count * 2);
        $this->save();
    }

    public function recordUnpublish(): void
    {
        $this->increment('unpublished_count');
        $this->decrement('confidence', 5);
        $this->save();
    }

    public function getCompositeScore(): float
    {
        return $this->approved_count * 10
             - $this->rejected_count * 15
             - $this->unpublished_count * 20
             + ($this->confidence * 0.5);
    }
}
