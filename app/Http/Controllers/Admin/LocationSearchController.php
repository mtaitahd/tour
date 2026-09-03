<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 3) {
            return response()->json([
                'error' => 'Please enter at least three characters',
            ], 422);
        }

        $cacheKey = 'nominatim:search:' . mb_strtolower($query);

        // Return cached results immediately if available.
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return empty($cached)
                ? response()->json(['results' => [], 'message' => 'No matching locations found'])
                : response()->json(['results' => $cached]);
        }

        // Serialize outgoing Nominatim requests via a cache lock.
        // Only one upstream request may be in-flight at a time.
        // If the lock cannot be acquired within 5 seconds the service is busy.
        $lockKey = 'nominatim:lock';
        $lock = Cache::lock($lockKey, 10);

        if (! $lock->get()) {
            // Another request is already querying Nominatim.
            return response()->json([
                'results'   => [],
                'message'   => 'Service temporarily busy, please retry',
                'retryable' => true,
            ], 429);
        }

        try {
            // Re-check cache after acquiring the lock (another request
            // may have populated it while we were waiting).
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return empty($cached)
                    ? response()->json(['results' => [], 'message' => 'No matching locations found'])
                    : response()->json(['results' => $cached]);
            }

            $uaName    = config('services.nominatim.user_agent_name', 'AfroVertexTours');
            $uaVersion = config('services.nominatim.user_agent_version', '1.0');
            $uaUrl     = config('services.nominatim.user_agent_url', 'https://afroverto.com');
            $uaContact = config('services.nominatim.user_agent_contact', 'info@afroverto.com');
            $userAgent = "{$uaName}/{$uaVersion} ({$uaUrl}; {$uaContact})";

            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => $userAgent])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'             => $query,
                    'format'        => 'jsonv2',
                    'limit'         => 5,
                    'countrycodes'  => 'tz',
                    'addressdetails' => 1,
                ]);

            if (! $response->successful()) {
                Cache::put($cacheKey, [], 3600);

                return response()->json(['results' => [], 'message' => 'No matching locations found']);
            }

            $items = $response->json();

            if (! is_array($items)) {
                Cache::put($cacheKey, [], 3600);

                return response()->json(['results' => [], 'message' => 'No matching locations found']);
            }

            $results = array_map(fn (array $item) => [
                'display_name' => $item['display_name'] ?? '',
                'lat'          => (float) ($item['lat'] ?? 0),
                'lng'          => (float) ($item['lon'] ?? $item['lng'] ?? 0),
            ], array_slice($items, 0, 5));

            Cache::put($cacheKey, $results, 3600);

            return empty($results)
                ? response()->json(['results' => [], 'message' => 'No matching locations found'])
                : response()->json(['results' => $results]);
        } catch (\Exception $e) {
            Cache::put($cacheKey, [], 3600);

            return response()->json(['results' => [], 'message' => 'No matching locations found']);
        } finally {
            $lock->release();
        }
    }
}
