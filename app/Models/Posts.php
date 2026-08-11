<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Posts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'image',
        'content',
        'counter',
        'status',
        'created_by',
        'category_id',
        'tags',
        'source',
        'domain',
        'meta_data',
        'updated_by',
        'published_at',
        // New fields
        'published_by',
        'unpublished_at',
        'unpublished_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at'  => 'datetime',
        'unpublished_at'=> 'datetime',
        'counter'       => 'integer',
        'meta_data'     => 'array',
    ];

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->decodeTags($value),
            set: fn($value) => json_encode(array_values($this->decodeTags($value))),
        );
    }

    private function decodeTags($value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }
        if ($value === null || $value === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        if (is_array($decoded)) {
            return array_values($decoded);
        }
        if (is_string($decoded)) {
            $inner = json_decode($decoded, true);
            if (is_array($inner)) {
                return array_values($inner);
            }
        }
        return [];
    }

    /**
     * Get the user who created the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the category that the post belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public static function getTrending($limit)
    {
        $posts = self::select('title', 'image', 'slug', 'counter', 'category_id')
            ->with('category')
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->inRandomOrder()   
            ->limit($limit)      
            ->get()
            ->sortByDesc('counter') 
            ->values();

        return $posts;
    }

}