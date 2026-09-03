<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\NewInquiryNotification;
use App\Mail\InquiryConfirmation;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Pricing\LevelCatalog;
use App\Services\TourPriceResolver;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'tour_package_id'   => 'nullable|exists:tour_packages,id',
            'package_level'     => 'nullable|string|max:50',
            'companions'        => 'nullable|string',
            'travel_date'       => 'nullable|date',
            'accommodation'     => 'nullable|in:SILVER,GOLD,PLATINUM',
            'room_type'         => 'nullable|string',
            'bed_type'          => 'nullable|string',
            'budget_min'        => 'nullable|numeric|min:0',
            'budget_max'        => 'nullable|numeric|min:0',
            'adult_age'         => 'nullable|string',
            'children_age'      => 'nullable|string',
            // Actual traveler headcount — separate from the age-range fields above,
            // which describe composition but not group size. Defaults match the
            // Inquiry table's own column defaults (1 adult, 0 children) so this stays
            // optional for forms that don't collect it.
            'adults'            => 'nullable|integer|min:1',
            'children'          => 'nullable|integer|min:0',
            'message'           => 'nullable|string',
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'email'             => 'required|email',
            'country'           => 'required|string',
            'phone'             => 'required|string|max:30',
            'g-recaptcha-response' => 'required|string',
        ]);

        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $validated['g-recaptcha-response'],
            'remoteip' => $request->ip(),
        ]);

        if (!$recaptchaResponse->successful() || !$recaptchaResponse->json('success')) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Please complete the reCAPTCHA verification and try again.',
            ]);
        }

        $tourPackage = !empty($validated['tour_package_id'])
            ? TourPackage::findOrFail($validated['tour_package_id'])
            : null;

        // Phase 3: freeze a server-generated price snapshot at submission time.
        // The exact group size is derived by the server (adults + children), the
        // season is resolved from the travel date, the amount is read straight
        // from configured package prices — never from browser-supplied numbers —
        // and total_amount is copied from the same server result.
        $quoteSnapshot = null;
        $totalAmount = null;
        if ($tourPackage) {
            $groupSize = (int) ($validated['adults'] ?? 1) + (int) ($validated['children'] ?? 0);
            $this->validatePackageLevel($tourPackage, $validated['package_level'] ?? null);

            $quoteSnapshot = app(TourPriceResolver::class)->snapshot($tourPackage, [
                'date'       => $validated['travel_date'] ?? null,
                'group_size' => $groupSize,
                'level_key'  => $validated['package_level'] ?? null,
            ], (int) ($validated['adults'] ?? 1), (int) ($validated['children'] ?? 0));

            if (($quoteSnapshot['request_type'] ?? null) === 'automatic') {
                $totalAmount = (float) $quoteSnapshot['group_total'];
            }
        }

        $inquiry = Inquiry::create([
            'tour_package_id'     => $tourPackage ? $tourPackage->id : null,
            'type'                => $tourPackage ? 'tour_booking' : 'general',
            'name'                => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'message'             => $validated['message'] ?? '',
            'preferred_start_date' => $validated['travel_date'] ?? null,
            'adults'              => $validated['adults'] ?? 1,
            'children'            => $validated['children'] ?? 0,
            'companions'          => $validated['companions'] ?? null,
            'accommodation'       => $validated['accommodation'] ?? null,
            'room_type'           => $validated['room_type'] ?? null,
            'bed_type'            => $validated['bed_type'] ?? null,
            'budget_min'          => $validated['budget_min'] ?? null,
            'budget_max'          => $validated['budget_max'] ?? null,
            'adult_age_range'     => $validated['adult_age'] ?? null,
            'children_age_range'  => $validated['children_age'] ?? null,
            'total_amount'        => $totalAmount,
            'quote_snapshot'      => $quoteSnapshot,
            'country'             => $validated['country'],
            'status'              => 'pending',
        ]);

        $adminEmail = Setting::get('site_email', 'info@afrovertextours.com');
        Mail::to($adminEmail)->send(new NewInquiryNotification($inquiry));
        Mail::to($inquiry->email)->send(new InquiryConfirmation($inquiry));

        return redirect()->back()->with('success', 'Thank you! Your travel proposal has been received. We will contact you soon.');
    }

    /**
     * Contextual package-level validation against the selected tour.
     *
     * Rules enforced for tour inquiries:
     *   - a submitted level must be a known catalog level;
     *   - it must be one the tour can actually offer (STANDARD only for
     *     single-day tours; the tour's category levels for multi-day tours;
     *     otherwise the level keys the tour has actually configured);
     *   - a multi-level tour must receive an explicit level — the server never
     *     silently falls back to the cheapest one when none is chosen.
     */
    private function validatePackageLevel(TourPackage $tour, ?string $submitted): void
    {
        $catalog = LevelCatalog::fromConfig(config('tour.level_catalog'));
        $configured = collect($tour->packagePrices ?? [])
            ->pluck('level_key')
            ->filter()
            ->unique()
            ->values();

        if ($tour->package_duration_type === 'single_day') {
            $allowed = array_keys($catalog->singleDayLevel());
        } elseif ((string) $tour->package_category !== ''
            && $catalog->levelsFor((string) $tour->package_category) !== []) {
            $allowed = array_keys($catalog->levelsFor((string) $tour->package_category));
        } else {
            $allowed = $configured->all();
        }

        if ($submitted === null || $submitted === '') {
            if ($configured->count() > 1) {
                throw ValidationException::withMessages([
                    'package_level' => 'Please choose a package level for this tour.',
                ]);
            }

            return;
        }

        if (! $catalog->hasLevel($submitted)) {
            throw ValidationException::withMessages([
                'package_level' => 'The selected package level is not valid.',
            ]);
        }

        if (! in_array($submitted, $allowed, true)) {
            throw ValidationException::withMessages([
                'package_level' => 'The selected package level is not available for this tour.',
            ]);
        }
    }
}
