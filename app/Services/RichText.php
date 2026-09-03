<?php

namespace App\Services;

/**
 * Safe rich-text rendering for trusted, admin-authored HTML.
 *
 * Strips everything except a small whitelist of formatting tags and harmless
 * link attributes, removes event handlers / scripting vectors, collapses the
 * Microsoft-Word cruft that rich-text editors love to paste, and decodes
 * entity-encoded content only as many times as necessary.
 */
class RichText
{
    /**
     * Tags that are allowed to survive sanitization.
     */
    protected static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'a',
    ];

    /**
     * Attributes permitted on specific tags. The <a> tag may keep href/title/target/rel.
     */
    protected static array $allowedAttrs = [
        'a' => ['href', 'title', 'target', 'rel'],
    ];

    /**
     * Tags removed completely (including their contents), never unwrapped.
     */
    protected static array $removeTags = [
        'script', 'style', 'iframe', 'object', 'embed', 'noscript', 'link', 'meta',
    ];

    /**
     * Render trusted rich text as sanitized HTML.
     */
    public static function sanitize($html): string
    {
        if ($html === null) {
            return '';
        }

        $value = self::decodeAsNeeded((string) $html);

        if (trim($value) === '') {
            return '';
        }

        $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $libxmlOptions = 0;
        foreach (['LIBXML_HTML_NOERROR', 'LIBXML_HTML_NODEFDTD', 'LIBXML_NOERROR', 'LIBXML_NOWARNING'] as $libxmlFlag) {
            if (defined($libxmlFlag)) {
                $libxmlOptions |= constant($libxmlFlag);
            }
        }
        $dom->loadHTML($value, $libxmlOptions);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        $xpath = new \DOMXPath($dom);
        $elements = [];
        foreach ($xpath->query('.//*', $body) as $el) {
            $elements[] = $el;
        }

        // Process deepest nodes first so unwrapping never disturbs a parent.
        usort($elements, static fn ($a, $b) => self::depth($b) - self::depth($a));

        foreach ($elements as $el) {
            $tag = strtolower($el->nodeName);

            if (in_array($tag, self::$removeTags, true)) {
                if ($el->parentNode) {
                    $el->parentNode->removeChild($el);
                }
                continue;
            }

            if (!in_array($tag, self::$allowedTags, true)) {
                self::unwrap($el);
                continue;
            }

            if ($el->hasAttributes()) {
                $attrs = [];
                foreach ($el->attributes as $attr) {
                    $attrs[] = $attr;
                }
                foreach ($attrs as $attr) {
                    $name = strtolower($attr->nodeName);

                    if ($tag === 'a') {
                        if (!in_array($name, self::$allowedAttrs['a'], true)) {
                            $el->removeAttribute($attr->nodeName);
                            continue;
                        }
                        if ($name === 'href' && !self::isSafeUrl($attr->nodeValue)) {
                            $el->removeAttribute($attr->nodeName);
                        }
                        if ($name === 'target' && strtolower(trim($attr->nodeValue)) === '_blank') {
                            $el->setAttribute('rel', 'noopener noreferrer');
                        }
                    } else {
                        $el->removeAttribute($attr->nodeName);
                    }
                }
            }
        }

        $clean = '';
        foreach ($body->childNodes as $node) {
            $clean .= $dom->saveHTML($node);
        }

        $clean = self::tidy($clean);

        return trim($clean);
    }

    /**
     * Strip to clean plain text for titles, breadcrumbs, meta descriptions, etc.
     */
    public static function toPlainText($html, int $limit = 0): string
    {
        $text = strip_tags((string) ($html ?? ''));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        if ($limit > 0 && mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit);
            $text = rtrim($text, ' .,;-') . '…';
        }

        return $text;
    }

    /**
     * Decode HTML entities only while the content still contains encoded tags
     * but no real tags — i.e. it was entity-encoded one or more times.
     */
    protected static function decodeAsNeeded(string $value): string
    {
        $guard = 0;
        while (
            $guard < 5
            && preg_match('/&(?:amp;)?(?:lt|gt|quot|#39|nbsp|mdash|ndash|rsquo|lsquo|hellip);/i', $value)
            && !preg_match('/<[a-z][\s\S]*>/i', $value)
        ) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
            $guard++;
        }

        return $value;
    }

    protected static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return true;
        }

        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');

        if ($scheme === '') {
            return true; // relative URL
        }

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    protected static function unwrap(\DOMElement $el): void
    {
        $parent = $el->parentNode;
        if (!$parent) {
            return;
        }
        while ($el->firstChild) {
            $child = $el->firstChild;
            $parent->insertBefore($child, $el);
        }
        $parent->removeChild($el);
    }

    protected static function depth(\DOMElement $el): int
    {
        $depth = 0;
        $node = $el->parentNode;
        while ($node && $node->nodeType === XML_ELEMENT_NODE) {
            $depth++;
            $node = $node->parentNode;
        }

        return $depth;
    }

    /**
     * Remove empty paragraphs, collapse repeated &nbsp; and trim stray whitespace.
     */
    protected static function tidy(string $html): string
    {
        // Drop paragraphs that contain only whitespace / &nbsp; / a single <br>.
        // DOMDocument converts &nbsp; into a real U+00A0 char, so match both forms.
        $html = preg_replace('/<p>[\s\x{00A0}]*(?:<br\s*\/?>)?[\s\x{00A0}]*<\/p>/iu', '', $html);

        // Collapse runs of multiple non-breaking spaces into one.
        $html = preg_replace('/(\x{00A0}|&nbsp;){2,}/u', '&nbsp;', $html);

        // Remove tabs / mso leftovers that survive as text.
        $html = preg_replace('/mso-[a-z-]+:\s*[^;]+;?/i', '', $html);

        return $html;
    }
}
