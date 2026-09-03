<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\GalleryImage;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index()
    {
        $posts = BlogPost::with('category')
                         ->orderByDesc('published_at')
                         ->paginate(15);

        return view('admin.blog-posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new blog post.
     */
    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog-posts.create', compact('categories'));
    }

    /**
     * Store a newly created blog post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|unique:blog_posts,slug',
            'content'          => 'required|string',
            'category_id'      => 'nullable|exists:blog_categories,id',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',
            'featured_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            // Media Library picker path — additive alongside featured_image above.
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'is_featured'      => 'boolean',
            'no_robots'        => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_featured'] = $request->has('is_featured');
        $validated['no_robots'] = $request->boolean('no_robots');

        $post = BlogPost::create($validated);

        // Upload featured image
        if ($request->hasFile('featured_image')) {
            $post->addMediaFromRequest('featured_image')
                 ->toMediaCollection('featured_image');
        }

        // Media Library selection, on create — no previous usage to forget here.
        if (! empty($validated['featured_image_id'])) {
            $image = GalleryImage::find($validated['featured_image_id']);
            if ($image) {
                app(MediaLibraryService::class)->recordUsage($image->id, $post, 'featured_image_id');
            }
        }

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.blog-posts.index')
                         ->with('success', 'Blog post created successfully!');
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(BlogPost $blogPost)
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog-posts.edit', compact('blogPost', 'categories'));
    }

    /**
     * Update the specified blog post in storage.
     */
    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:blog_posts,slug,' . $blogPost->id,
            'content'          => 'required|string',
            'category_id'      => 'nullable|exists:blog_categories,id',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',
            'featured_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            // Media Library picker path — additive alongside featured_image above.
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'is_featured'      => 'boolean',
            'no_robots'        => 'nullable|boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['no_robots'] = $request->boolean('no_robots');

        // Captured before update() runs, for the same reason explained in
        // PageController::update() — getOriginal()/the model's pre-update FK value
        // stays correct after update() since Eloquent's save() only syncs $changes,
        // not $original, but reading it into a local variable up front keeps this
        // logic easy to follow regardless.
        $previousFeaturedId = $blogPost->featured_image_id;

        $blogPost->update($validated);

        if ($request->filled('featured_image_id') && (int) $request->input('featured_image_id') !== (int) $previousFeaturedId) {
            $mediaLibrary = app(MediaLibraryService::class);

            if ($previousFeaturedId) {
                $oldImage = GalleryImage::find($previousFeaturedId);
                if ($oldImage) {
                    $mediaLibrary->forgetUsage($oldImage->id, $blogPost, 'featured_image_id');
                }
            }

            $newImage = GalleryImage::find($validated['featured_image_id']);
            if ($newImage) {
                $mediaLibrary->recordUsage($newImage->id, $blogPost, 'featured_image_id');
            }
        }

        // Replace featured image if new one is uploaded
        if ($request->hasFile('featured_image')) {
            $blogPost->clearMediaCollection('featured_image');
            $blogPost->addMediaFromRequest('featured_image')
                     ->toMediaCollection('featured_image');
        }

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.blog-posts.index')
                         ->with('success', 'Blog post updated successfully!');
    }

    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(BlogPost $blogPost)
    {
        // See TourPackageController::destroy() for the full rationale.
        app(\App\Services\MediaLibraryService::class)->forgetAllUsagesFor($blogPost);

        $blogPost->delete();

        \App\Services\SitemapGenerator::generate();

        return redirect()->route('admin.blog-posts.index')
                         ->with('success', 'Blog post deleted successfully!');
    }
}