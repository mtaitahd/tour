<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManagerList;
use App\Models\Page;
use App\Models\TourCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManagerListController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.manager-lists.tours');
    }

    public function tours()
    {
        return $this->listing('tours');
    }

    public function pages()
    {
        return $this->listing('pages');
    }

    private function listing(string $type)
    {
        $lists = ManagerList::where('content_type', $type)->orderBy('order')->orderBy('title')->paginate(25);

        return view('admin.manager-lists.index', ['lists' => $lists, 'listType' => $type]);
    }

    public function create(Request $request)
    {
        $type = $request->query('type') === 'pages' ? 'pages' : 'tours';
        return view('admin.manager-lists.form', $this->formData(new ManagerList(['content_type' => $type, 'status' => 'draft'])));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug(($validated['slug'] ?? '') ?: $validated['title']);
        $validated = $this->normaliseContent($validated);
        ManagerList::create($validated);

        $redirect = route($validated['content_type'] === 'pages' ? 'admin.manager-lists.pages' : 'admin.manager-lists.tours');
        return $request->expectsJson()
            ? response()->json(['message' => 'Listing created successfully.', 'redirect' => $redirect])
            : redirect($redirect)->with('success', 'Listing created successfully.');
    }

    public function edit(ManagerList $managerList)
    {
        return view('admin.manager-lists.form', $this->formData($managerList));
    }

    public function update(Request $request, ManagerList $managerList)
    {
        $validated = $this->validated($request, $managerList);
        $validated['slug'] = $this->uniqueSlug(($validated['slug'] ?? '') ?: $validated['title'], $managerList->id);
        $managerList->update($this->normaliseContent($validated));

        $redirect = route($managerList->content_type === 'pages' ? 'admin.manager-lists.pages' : 'admin.manager-lists.tours');
        return $request->expectsJson()
            ? response()->json(['message' => 'Listing updated successfully.', 'redirect' => $redirect])
            : redirect($redirect)->with('success', 'Listing updated successfully.');
    }

    public function destroy(ManagerList $managerList)
    {
        $type = $managerList->content_type;
        $managerList->delete();

        return redirect()->route($type === 'pages' ? 'admin.manager-lists.pages' : 'admin.manager-lists.tours')->with('success', 'Listing deleted.');
    }

    private function validated(Request $request, ?ManagerList $list = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('manager_lists', 'slug')->ignore($list?->id)],
            'content_type' => ['required', $list ? Rule::in([$list->content_type]) : Rule::in(['tours', 'pages'])],
            'caption' => ['nullable', 'string', 'max:500'],
            'introduction' => ['nullable', 'string'],
            'category_ids' => ['required_if:content_type,tours', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'distinct', 'exists:tour_categories,id'],
            'page_ids' => ['required_if:content_type,pages', 'array', 'min:1'],
            'page_ids.*' => ['integer', 'distinct', 'exists:pages,id'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'string', 'max:5000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'order' => ['nullable', 'integer', 'min:0'],
            'no_robots' => ['nullable', 'boolean'],
        ]);

        return $data + ['no_robots' => false, 'order' => 0];
    }

    private function normaliseContent(array $data): array
    {
        $data['order'] = (int) ($data['order'] ?? 0);
        $data['no_robots'] = (bool) ($data['no_robots'] ?? false);
        $data['category_ids'] = $data['content_type'] === 'tours' ? array_values($data['category_ids'] ?? []) : [];
        $data['page_ids'] = $data['content_type'] === 'pages' ? array_values($data['page_ids'] ?? []) : [];
        $data['faqs'] = collect($data['faqs'] ?? [])
            ->map(fn ($faq) => ['question' => trim((string) ($faq['question'] ?? '')), 'answer' => trim((string) ($faq['answer'] ?? ''))])
            ->filter(fn ($faq) => $faq['question'] !== '' && $faq['answer'] !== '')
            ->values()->all();
        return $data;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'listing';
        $slug = $base;
        $suffix = 2;
        while (ManagerList::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $suffix++;
        }
        return $slug;
    }

    private function formData(ManagerList $managerList): array
    {
        // Every option — the grouped taxonomy rows (country, region, tour
        // type, duration) and the legacy listing categories — lives in
        // tour_categories, so the form ticks them all into the same
        // category_ids column the public /collections/{slug} scope reads.
        // Rows are offered regardless of status so an option deactivated
        // after being ticked still shows on edit instead of vanishing.
        $categoryGroups = collect(TourCategory::FORM_GROUPS)
            ->mapWithKeys(fn (string $label, string $type) => [
                $type => TourCategory::where('type', $type)
                    ->orderBy('order')->orderBy('name')->get(['id', 'name', 'status']),
            ]);

        return [
            'managerList' => $managerList,
            'listType' => $managerList->content_type,
            'categoryGroups' => $categoryGroups,
            'categories' => TourCategory::where('type', TourCategory::TYPE_CATEGORY)
                ->orderBy('order')->orderBy('name')->get(['id', 'name']),
            'pages' => Page::where('status', 'published')->whereNotIn('slug', Page::siteInfoSlugs())->orderBy('title')->get(['id', 'title', 'slug']),
        ];
    }
}
