<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * The published tours this listing is allowed to show.
     *
     * category_ids holds ids from every tour_categories group, so the ids are
     * first grouped by their type: a tour must sit inside every selected group
     * (AND across country / region / tour type / duration / listing categories)
     * but only needs one option within each group (OR inside). A list ticked
     * "Tanzania" + "Private" therefore shows only Tanzania private tours, while
     * "Tanzania" + "Kenya" shows tours from either country. A list that only
     * ticks legacy listing categories is a single group, so it keeps the
     * original any-of behaviour unchanged.
     *
     * A listing with nothing selected scopes to no tours at all, matching the
     * previous placeholder-id behaviour instead of accidentally widening to
     * every tour on the site.
     */
    public function scopedTours(): Builder
    {
        $query = TourPackage::where('status', 'published')->where('no_robots', false);

        $idsByGroup = TourCategory::whereIn('id', $this->category_ids ?: [0])
            ->get(['id', 'type'])
            ->groupBy('type')
            ->map(fn ($categories) => $categories->pluck('id')->all());

        if ($idsByGroup->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $tours) use ($idsByGroup) {
            foreach ($idsByGroup as $ids) {
                $tours->whereHas('categories', fn (Builder $categories) => $categories->whereIn('tour_categories.id', $ids));
            }
        });
    }
}
