<?php

namespace App\Models;

use App\Models\Concerns\AutoCurrentYearTitle;
use Illuminate\Database\Eloquent\Model;

class BlogPostTranslation extends Model
{
    use AutoCurrentYearTitle;

    /**
     * The table associated with the model.
     * (Explicitly set for clarity and safety)
     */
    protected $table = 'blog_post_translations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'post_id',
        'title',
        'slug',
        'language_code',
    ];

    /**
     * Relationship to the original blog post.
     */
    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }
}