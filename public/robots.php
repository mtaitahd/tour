<?php
/**
 * robots.txt — Dynamic, zero-dependency generator.
 *
 * This file is served as /robots.txt via the .htaccess RewriteRule.
 * It requires NO database, NO framework, NO sessions — just plain PHP.
 *
 * The sitemap URL is auto-detected from the current host.
 * No admin UI needed. No settings key. Just works.
 */

header('Content-Type: text/plain; charset=utf-8');

// ── Auto-detect base URL ──────────────────────────────────────────────────
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'afrovertextours.com';
$baseUrl = $scheme . '://' . $host;

// ── Robots.txt content ────────────────────────────────────────────────────
echo "User-agent: *\n";
echo "Allow: /\n";
echo "\n";

// Block private / system directories
echo "Disallow: /admin/\n";
echo "Disallow: /login/\n";
echo "Disallow: /dashboard/\n";
echo "Disallow: /api/\n";
echo "Disallow: /storage/\n";
echo "\n";

// Sitemap
echo "Sitemap: {$baseUrl}/sitemap.xml\n";
