<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerList extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'content_type', 'caption', 'introduction',
        'category_ids', 'page_ids', 'faqs', 'meta_title', 'meta_description',
        'meta_keywords', 'no_robots', 'status', 'order',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'page_ids' => 'array',
        'faqs' => 'array',
        'no_robots' => 'boolean',
    ];
}
