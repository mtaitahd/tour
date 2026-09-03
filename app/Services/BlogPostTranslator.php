<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class BlogPostTranslator
{
    protected $languages = [
        'sw' => 'Swahili',
        'fr' => 'French',
        'es' => 'Spanish',
        'de' => 'German',
        'zh' => 'Chinese',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'nl' => 'Dutch',
    ];

    /**
     * Translate ONE post — title + slug for all non-English languages
     */
    public function translatePost(BlogPost $post)
    {
        foreach ($this->languages as $code => $name) {

            // Skip if translation already exists
            if (BlogPostTranslation::where('post_id', $post->id)
                                  ->where('language_code', $code)
                                  ->exists()) {
                continue;
            }

            // Translate the title
            $translatedTitle = $this->translateText($post->title, $code);

            // Fallback if translation fails
            if (empty($translatedTitle)) {
                $translatedTitle = $post->title . " (" . $name . ")";
            }

            // Generate proper translated slug
            $translatedSlug = $this->createTranslatedSlug($translatedTitle, $code, $post->id);

            BlogPostTranslation::create([
                'post_id'       => $post->id,
                'title'         => $translatedTitle,
                'slug'          => $translatedSlug,
                'language_code' => $code,
            ]);
        }
    }

    /**
     * Translate ALL posts (only missing languages)
     */
    public function translateAllPosts()
    {
        $posts = BlogPost::all();

        foreach ($posts as $post) {
            $this->translatePost($post);
        }
    }

    /**
     * Free Google Translate - NO TIMEOUT
     */
    private function translateText(string $text, string $targetLang): string
    {
        try {
            $response = Http::get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl'     => 'auto',
                'tl'     => $targetLang,
                'dt'     => 't',
                'q'      => $text,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result[0][0][0] ?? $text;
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return $text; // fallback
    }

    /**
     * Improved Slug Generator - Especially good for Chinese
     */
    private function createTranslatedSlug(string $translatedTitle, string $languageCode, int $postId): string
    {
        if ($languageCode === 'zh') {
            // Special handling for Chinese - keeps Chinese characters
            $slug = preg_replace('/\s+/u', '-', trim($translatedTitle));           // replace spaces with -
            $slug = preg_replace('/[^\p{Han}\p{L}\p{N}-]/u', '', $slug);         // keep Chinese, letters, numbers, hyphen
            $slug = preg_replace('/-+/', '-', $slug);                             // remove multiple hyphens
            $slug = trim($slug, '-');

            return $slug ?: 'post-' . $postId . '-zh';
        }

        // Normal languages (Swahili, French, Spanish, etc.)
        return Str::slug($translatedTitle, '-');
    }
}