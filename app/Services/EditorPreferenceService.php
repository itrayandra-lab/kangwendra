<?php

namespace App\Services;

use App\Models\EditorPreference;
use App\Models\ResearchRecommendation;
use Illuminate\Support\Facades\Log;

class EditorPreferenceService
{
    /**
     * Record that editor APPROVED (picked) this URL
     */
    public function recordApproval(string $keyword, string $topic, string $url, float $confidence): void
    {
        $pref = $this->getOrCreatePreference($keyword, $topic);
        $pref->recordApproval();

        Log::info('EditorPreference: approved', [
            'keyword' => $keyword,
            'topic'   => $topic,
            'url'     => $url,
            'score'   => $pref->score,
            'confidence' => $pref->confidence,
        ]);
    }

    /**
     * Record that editor REJECTED this URL
     */
    public function recordRejection(string $keyword, string $url, ?string $topic = null): void
    {
        $pref = $this->getOrCreatePreference($keyword, $topic ?? '');
        $pref->addToBlocklist($url);
        $pref->recordRejection($topic);

        Log::info('EditorPreference: rejected', [
            'keyword' => $keyword,
            'url'     => $url,
            'topic'   => $topic,
            'score'   => $pref->score,
            'confidence' => $pref->confidence,
        ]);
    }

    /**
     * Record that a published post was UNPUBLISHED
     */
    public function recordUnpublish(string $keyword): void
    {
        $pref = EditorPreference::where('keyword', strtolower(trim($keyword)))->first();
        if ($pref) {
            $pref->recordUnpublish();
            Log::info('EditorPreference: unpublish recorded', [
                'keyword'     => $keyword,
                'confidence'  => $pref->confidence,
            ]);
        }
    }

    /**
     * Calculate confidence score for a URL given keyword
     */
    public function calculateConfidence(string $keyword, string $url, string $domain): float
    {
        $pref = EditorPreference::where('keyword', strtolower(trim($keyword)))->first();

        // No history = default confidence
        if (!$pref) {
            return 50.0;
        }

        // Check blocklist first
        if ($pref->isBlocked($url)) {
            Log::debug('EditorPreference: URL is blocked', ['url' => $url]);
            return 0;
        }

        $score = $pref->confidence;

        // Domain bonus: SEL/SEJ domains get boost
        if (stripos($domain, 'searchengineland.com') !== false) {
            $score += 5;
        }
        if (stripos($domain, 'searchenginejournal.com') !== false) {
            $score += 5;
        }

        // Positive signals: high historical approval
        if ($pref->approved_count >= 5) {
            $score += 5;
        }
        if ($pref->approved_count >= 10) {
            $score += 5;
        }

        // Negative signals: many rejections
        if ($pref->rejected_count >= 3) {
            $score -= 5;
        }
        if ($pref->rejected_count >= 5) {
            $score -= 10;
        }

        // Negative signals: unpublishes
        if ($pref->unpublished_count >= 1) {
            $score -= 8;
        }
        if ($pref->unpublished_count >= 3) {
            $score -= 15;
        }

        // Cap at 0-100
        return max(0, min(100, $score));
    }

    /**
     * Get top N keywords by confidence for auto-research
     */
    public function getTopKeywords(int $limit = 3): array
    {
        return EditorPreference::topKeywords($limit)
            ->pluck('keyword')
            ->toArray();
    }

    /**
     * Check if URL is in any blocklist
     */
    public function isBlocked(string $url): bool
    {
        $prefs = EditorPreference::all();
        foreach ($prefs as $pref) {
            if ($pref->isBlocked($url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Initialize preference for a keyword (when it's first used)
     */
    public function initializeKeyword(string $keyword): EditorPreference
    {
        return EditorPreference::firstOrCreate(
            ['keyword' => strtolower(trim($keyword))],
            [
                'score'      => 0,
                'confidence' => 50,
            ]
        );
    }

    /**
     * Get or create preference record
     */
    protected function getOrCreatePreference(string $keyword, ?string $topic): EditorPreference
    {
        $pref = EditorPreference::firstOrCreate(
            ['keyword' => strtolower(trim($keyword))],
            [
                'topic'      => $topic,
                'score'      => 0,
                'confidence' => 50,
            ]
        );

        // Update topic if provided
        if ($topic && empty($pref->topic)) {
            $pref->topic = $topic;
            $pref->save();
        }

        return $pref;
    }

    /**
     * Get all recommendations for a keyword with confidence scores
     */
    public function rankRecommendations(string $keyword, array $recommendations): array
    {
        foreach ($recommendations as &$rec) {
            $url = $rec['url'] ?? '';
            $domain = parse_url($url, PHP_URL_HOST) ?? '';
            $aiScore = $rec['confidence_score'] ?? 50;
            $prefScore = $this->calculateConfidence($keyword, $url, $domain);

            // Blend AI score with preference score (weighted average)
            $rec['final_score'] = ($aiScore * 0.6) + ($prefScore * 0.4);
        }

        // Sort by final score descending
        usort($recommendations, fn($a, $b) => ($b['final_score'] ?? 0) <=> ($a['final_score'] ?? 0));

        return $recommendations;
    }

    /**
     * Check if AI should auto-select (confidence >= threshold)
     */
    public function shouldAutoSelect(string $keyword, string $url, string $domain, float $threshold = 85): bool
    {
        if ($this->isBlocked($url)) {
            return false;
        }

        $confidence = $this->calculateConfidence($keyword, $url, $domain);
        return $confidence >= $threshold;
    }
}
