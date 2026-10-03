<?php

namespace App\Http\Controllers;

use App\Models\ManagerList;
use App\Models\Page;
use App\Models\TourPackage;

class ManagerListController extends Controller
{
    public function show(string $slug)
    {
        $managerList = ManagerList::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return $this->renderListing($managerList);
    }

    /** This action is registered only inside the permission-protected admin routes. */
    public function preview(ManagerList $managerList)
    {
        // Prevent search engines from indexing this admin-only rendering,
        // including when the listing itself is already published.
        $managerList->setAttribute('no_robots', true);
        return $this->renderListing($managerList, true);
    }

    private function renderListing(ManagerList $managerList, bool $previewMode = false)
    {
        $items = collect();

        if ($managerList->content_type === 'tours') {
            $items = TourPackage::where('status', 'published')
                ->where('no_robots', false)
                ->whereHas('categories', fn ($query) => $query->whereIn('tour_categories.id', $managerList->category_ids ?: [0]))
                ->with(['categories', 'destinations'])
                ->orderByDesc('is_featured')->orderBy('order')->orderBy('title')->paginate(12);
        } else {
            $items = Page::where('status', 'published')
                ->where('no_robots', false)
                ->whereIn('id', $managerList->page_ids ?: [0])
                ->whereNotIn('slug', Page::siteInfoSlugs())
                ->orderBy('order')->orderBy('title')->paginate(12);
        }

        $faqs = collect($managerList->faqs ?? [])->filter(fn ($faq) => trim((string) ($faq['question'] ?? '')) !== '' && trim((string) ($faq['answer'] ?? '')) !== '')->values();
        $meta = [
            'title' => $managerList->meta_title ?: $managerList->title . ' | Afro-Vertex Tours & Safaris',
            'description' => $managerList->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($managerList->caption ?: $managerList->introduction)), 160),
            'keywords' => $managerList->meta_keywords ?: 'East Africa tours, safaris, travel information',
            'canonical' => route('manager-lists.show', $managerList->slug),
            'no_robots' => $managerList->no_robots,
        ];

        return view('frontend.manager-lists.show', compact('managerList', 'items', 'faqs', 'meta', 'previewMode'));
    }
}
