<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Support\Str;
use App\Services\BlogPostTranslator;
use App\Models\BlogPostTranslation;        // ← Correct model for translations

class BlogPostController extends Controller
{
    /**
     * ADMIN INDEX
     */
    public function index()
    {
        $posts = BlogPost::with('category')
                        ->latest()
                        ->paginate(10);
        return view('admin.blog-posts.index', compact('posts'));
    }

    /**
     * ADMIN - CREATE NEW POST + Auto Translation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:draft,published',
            'published_at'=> 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        $post = BlogPost::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content' => $validated['content'],
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'featured_image' => $request->hasFile('featured_image')
                                ? $request->file('featured_image')->store('blog', 'public')
                                : null,
        ]);

        // ==================== AUTO TRANSLATION ====================
        try {
            $translator = new BlogPostTranslator();
            $translator->translatePost($post);
        } catch (\Exception $e) {
            \Log::error('Translation failed for new post ID: ' . $post->id);
        }
        // =========================================================

        return redirect()->route('admin.blog-posts.index')
                         ->with('success', 'Blog post created and translated successfully!');
    }

    /**
     * ADMIN - UPDATE POST + Re-translate
     */
    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:draft,published',
            'published_at'=> 'nullable|date',
        ]);

        $blogPost->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content' => $validated['content'],
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
        ]);

        // ==================== RE-TRANSLATE ====================
        try {
            $translator = new BlogPostTranslator();
            $translator->translatePost($blogPost);
        } catch (\Exception $e) {
            \Log::error('Translation failed during update for post ID: ' . $blogPost->id);
        }
        // ====================================================

        return redirect()->route('admin.blog-posts.index')
                         ->with('success', 'Blog post updated and translations refreshed!');
    }

    /**
     * PUBLIC - Blog Listing
     */
    public function indexPublic(Request $request)
    {
        $query = BlogPost::where('status', 'published')
                         ->with('category')
                         ->orderByDesc('published_at');

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('content', 'like', $search);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('published_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('published_at', '<=', $request->date_to);
        }

        $posts = $query->paginate(9)->withQueryString();
        $categories = BlogCategory::orderBy('name')->get();

        return view('frontend.blog.index', compact('posts', 'categories'));
    }

    /**
     * PUBLIC - Single Blog Post
     */
    public function showPublic($slug)
    {
        $post = BlogPost::where('slug', $slug)
                        ->where('status', 'published')
                        ->with('category')
                        ->firstOrFail();

        $relatedPosts = BlogPost::where('status', 'published')
                                ->where('id', '!=', $post->id)
                                ->when($post->category_id, function ($query) use ($post) {
                                    $query->where('category_id', $post->category_id);
                                })
                                ->orderByDesc('published_at')
                                ->take(4)
                                ->get();

        if ($relatedPosts->isEmpty()) {
            $relatedPosts = BlogPost::where('status', 'published')
                                    ->where('id', '!=', $post->id)
                                    ->where('is_featured', true)
                                    ->orderByDesc('published_at')
                                    ->take(4)
                                    ->get();
        }

        return view('frontend.blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * PUBLIC - Single TRANSLATED Blog Post
     * URL: /blog/{language_code}/{slug}   Example: /blog/fr/mon-article-super
     */
   public function showTranslated($language_code, $slug)
{
    // Find the translation by language + slug
    $translation = BlogPostTranslation::where('language_code', $language_code)
        ->where('slug', $slug)
        ->firstOrFail();

    // Load the full original post using post_id
    $post = $translation->post;

    // Security check
    if (!$post || $post->status !== 'published') {
        abort(404);
    }

    // Related posts
    $relatedPosts = BlogPost::where('id', '!=', $post->id)
        ->where('status', 'published')
        ->latest()
        ->limit(4)
        ->get();

    // ✅ CORRECTED VIEW PATH (matches your folder structure)
    return view('frontend.blog.translated', compact('post', 'translation', 'relatedPosts'));
}
}