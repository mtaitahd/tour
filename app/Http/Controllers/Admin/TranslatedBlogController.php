<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPostTranslation;   // ← Make sure this model exists (see note below)
use Illuminate\Http\Request;

class TranslatedBlogController extends Controller
{
    /**
     * Display a listing of all blog post translations.
     * Shows ONLY: Title, Slug, Language Code + Actions (as requested)
     */
    public function index()
    {
        $translations = BlogPostTranslation::with('post')           // optional: loads original post info
            ->select('id', 'post_id', 'title', 'slug', 'language_code', 'created_at')
            ->latest()
            ->paginate(15);

        return view('admin.translated-blogs.index', compact('translations'));
    }

    /**
     * Show the form for editing a translation.
     */
    public function edit(BlogPostTranslation $translatedBlog)
    {
        return view('admin.translated-blogs.edit', compact('translatedBlog'));
    }

    /**
     * Update the specified translation.
     */
    public function update(Request $request, BlogPostTranslation $translatedBlog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|max:255|unique:blog_post_translations,slug,' . $translatedBlog->id,
        ]);

        $translatedBlog->update($request->only(['title', 'slug']));

        return redirect()->route('admin.translated-blogs.index')
                         ->with('success', 'Translation updated successfully!');
    }

    /**
     * Remove the specified translation from storage.
     */
    public function destroy(BlogPostTranslation $translatedBlog)
    {
        $translatedBlog->delete();

        return redirect()->route('admin.translated-blogs.index')
                         ->with('success', 'Translation deleted successfully.');
    }

    // You can leave create/store/show empty for now or add them later if needed
}