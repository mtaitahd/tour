<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaCompressionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin "Compress Images" tool, modelled on the kizza-tours Image Manager.
 *
 * It does NOT re-encode the uploaded originals — per requirement the original is kept
 * exactly as uploaded. Instead it re-compresses the WebP conversion variants Spatie
 * Media Library already generates (thumb-webp, medium-webp, large-webp) so the images
 * served to visitors are smaller. The admin chooses either a fixed quality or a target
 * file size (KB), and the page groups the library by collection (gallery / hero /
 * tours ...) so images can be compressed per category.
 */
class MediaCompressionController extends Controller
{
    public function __construct(protected MediaCompressionService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.media.compression', [
            'categories' => $this->service->summary(),
            'totalImages' => \App\Models\GalleryImage::query()
                ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                ->count(),
        ]);
    }

    /**
     * Run the compression. Accepts either a list of media ids, or 'all'.
     */
    public function run(Request $request)
    {
        // Compressing hundreds of WebP variants (up to 3 conversions each) can run
        // for minutes inside one request. The web-server default execution ceiling
        // (XAMPP: max_execution_time=120) used to kill the job mid-way, leaving the
        // page stuck on "Compressing…". Lift the limits so it can run to completion.
        set_time_limit(0);
        if (ini_get('memory_limit') && ini_get('memory_limit') !== '-1') {
            @ini_set('memory_limit', '768M');
        }

        $request->validate([
            'mode' => ['required', 'in:quality,filesize'],
            'value' => ['nullable', 'integer', 'min:1'],
            'collection' => ['nullable', 'string'],
        ]);

        $mode = $request->input('mode');
        $value = (int) $request->input('value');

        // Fallback when the hidden value field wasn't populated (e.g. JS disabled) —
        // derive it from whichever mode-specific input was submitted.
        if ($value <= 0) {
            $value = (int) ($mode === 'filesize'
                ? $request->input('filesize_value')
                : $request->input('quality_value'));
        }
        if ($value <= 0) {
            $value = $mode === 'filesize' ? 10 : 70;
        }

        if ($mode === 'filesize' && $value > 10240) {
            $value = 10240; // cap target at 10 MB, purely as a sanity bound
        }

        $images = $this->resolveImages($request);

        if ($images->isEmpty()) {
            return back()->with('error', 'No images matched the selection.');
        }

        $results = $this->service->compress($images->pluck('id')->all(), $mode, $value);

        $origTotal = array_sum(array_column($results, 'orig'));
        $newTotal = array_sum(array_column($results, 'new'));
        $saved = $origTotal - $newTotal;
        $shrunk = collect($results)->filter(fn ($r) => $r['new'] < $r['orig'])->count();

        return redirect()
            ->route('admin.media.compression')
            ->with('success', 'Compressed ' . count($results) . ' image(s). Savings: ' . $this->human($saved) . '.')
            ->with('compression_result', [
                'mode' => $mode,
                'value' => $value,
                'results' => $results,
                'origTotal' => $origTotal,
                'newTotal' => $newTotal,
                'saved' => $saved,
                'shrunk' => $shrunk,
            ]);
    }

    /**
     * Resolve the media items to compress: an explicit id list, or every image within
     * the chosen collection(s), or all images when the admin picks "all".
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\GalleryImage>
     */
    protected function resolveImages(Request $request): \Illuminate\Support\Collection
    {
        if ($request->filled('media_ids')) {
            $ids = collect((array) $request->input('media_ids'))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id);

            return \App\Models\GalleryImage::query()->whereIn('id', $ids)->get();
        }

        if ($request->input('collection') && $request->input('collection') !== 'all') {
            return \App\Models\GalleryImage::query()
                ->where('collection_name', $request->input('collection'))
                ->get();
        }

        return \App\Models\GalleryImage::query()->get();
    }

    protected function human(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 2) . ' MB'
            : number_format($bytes / 1024, 1) . ' KB';
    }
}
