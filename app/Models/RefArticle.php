<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefArticle extends Model
{
    protected $fillable = [
        'source_url',
        'source_domain',
        'title',
        'content',
        'image_url',
        'tags',
        'author',
        'published_at',
        'ai_status',
        'ai_error',
        'generated_post_id',
        'batch_id',
    ];

    protected $casts = [
        'tags'         => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Artikel baru yang dihasilkan dari referensi ini.
     */
    public function generatedPost()
    {
        return $this->belongsTo(Posts::class, 'generated_post_id');
    }

    /* ── Scopes ── */

    public function scopePending($query)
    {
        return $query->where('ai_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('ai_status', 'processing');
    }

    public function scopeDone($query)
    {
        return $query->where('ai_status', 'done');
    }

    public function scopeFailed($query)
    {
        return $query->where('ai_status', 'failed');
    }
}
