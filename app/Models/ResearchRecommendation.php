<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchRecommendation extends Model
{
    protected $fillable = [
        'keyword',
        'url',
        'title',
        'domain',
        'snippet',
        'confidence_score',
        'status',
        'ref_article_id',
    ];

    protected $casts = [
        'confidence_score' => 'float',
    ];

    // ── Scopes ──

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeScraped($query)
    {
        return $query->where('status', 'scraped');
    }

    public function scopeByKeyword($query, string $keyword)
    {
        return $query->where('keyword', $keyword);
    }

    public function scopeHighConfidence($query, float $min = 85)
    {
        return $query->where('confidence_score', '>=', $min);
    }

    // ── Relations ──

    public function refArticle()
    {
        return $this->belongsTo(RefArticle::class, 'ref_article_id');
    }
}
