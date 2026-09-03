<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TourPriceCalculatorPreviewRequest;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;

/**
 * POST-only price calculator preview. Accepts the raw calculator payload,
 * validates it through the pure engine, and returns server-rendered preview
 * HTML plus a compact JSON summary. Always renders from the server result —
 * a stale client copy (detected via the payload digest) is only ever cosmetic;
 * persistence recomputes from the raw payload regardless.
 */
class TourPriceCalculatorController extends Controller
{
    public function preview(TourPriceCalculatorPreviewRequest $request): JsonResponse
    {
        $service = new PricingService();
        $payload = $request->validatedPayload();
        [$input, $serialized] = $service->calculateWithInput($payload);

        $html = view('admin.tour-packages.partials.pricing.preview', [
            'results' => $serialized,
            'currency' => strtoupper((string) ($payload['currency'] ?? 'USD')),
        ])->render();

        return response()->json([
            'ok' => true,
            'html' => $html,
            'summary' => $this->summarize($serialized),
            'payload_digest' => md5((string) json_encode($payload)),
        ]);
    }

    /** @param array<string, mixed> $serialized */
    private function summarize(array $serialized): array
    {
        $summary = [];

        foreach ($serialized['groups'] ?? [] as $season => $levelGroups) {
            foreach ($levelGroups as $levelKey => $sizes) {
                foreach ($sizes as $clients => $group) {
                    $summary[$season][$levelKey][(string) $clients] = $group['final_per_person'] ?? '0.00';
                }
            }
        }

        return $summary;
    }
}