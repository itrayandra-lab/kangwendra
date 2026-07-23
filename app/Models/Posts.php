<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
        'counter'     => 'integer',
        'tags'        => 'array',
        'meta_data'   => 'array',
    ];

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