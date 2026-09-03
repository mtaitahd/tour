<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourPackage;
use App\Models\Destination;
use App\Models\BlogPost;
use App\Models\Accommodation;
use App\Models\Testimonial;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
        {
            $featuredTours = TourPackage::where('status', 'published')
                                        ->where('is_featured', true)
                                        ->orderBy('order')
                                        ->take(3)
                                        ->with(['destinations', 'categories'])
                                        ->get();

            // Real published-tours total for the "Explore All N Safari Tours" button.
            $toursCount = TourPackage::where('status', 'published')->count();

            // Real operator trust data for the card footer — from published
            // testimonials only; never invented.
            $operatorReviews = Testimonial::published()->general()->count();
            $avgOperatorRating = $operatorReviews > 0
                ? (float) Testimonial::published()->general()->avg('rating')
                : 0.0;

            $featuredDestinations = Destination::where('is_featured', true)
                                               ->orderBy('order')
                                               ->take(8)
                                               ->get();

            $latestPosts = BlogPost::where('status', 'published')
                                   ->orderByDesc('published_at')
                                   ->take(3)
                                   ->with('featuredImage')
                                   ->get();

            $accommodations = Accommodation::published()
                                           ->featured()
                                           ->orderBy('order')
                                           ->take(6)
                                           ->get();

            $testimonials = Testimonial::published()
                                        ->general()
                                        ->orderBy('order')
                                        ->take(6)
                                        ->get();

            // Gallery images for the homepage "Safari Cinema Wall" section.
            // Each image is paired with its parent destination/tour for accurate metadata.
            $galleryData = collect();

            foreach ($featuredDestinations as $dest) {
                $imgs = $dest->galleryImages()->take(6);
                if ($imgs->isNotEmpty()) {
                    foreach ($imgs as $img) {
                        $galleryData->push(['image' => $img, 'parent' => $dest]);
                    }
                }
                if ($galleryData->count() >= 10) {
                    break;
                }
            }

            if ($galleryData->count() < 10) {
                foreach ($featuredTours as $tour) {
                    $imgs = $tour->galleryImages()->take(6);
                    if ($imgs->isNotEmpty()) {
                        $parent = $tour->destinations->first() ?: $tour;
                        foreach ($imgs as $img) {
                            $galleryData->push(['image' => $img, 'parent' => $parent]);
                        }
                    }
                    if ($galleryData->count() >= 10) {
                        break;
                    }
                }
            }

            if ($galleryData->count() < 6) {
                $extraDests = Destination::where('is_featured', false)
                    ->orderBy('order')
                    ->take(5)
                    ->get();
                foreach ($extraDests as $dest) {
                    $imgs = $dest->galleryImages()->take(4);
                    if ($imgs->isNotEmpty()) {
                        foreach ($imgs as $img) {
                            $galleryData->push(['image' => $img, 'parent' => $dest]);
                        }
                    }
                    if ($galleryData->count() >= 10) {
                        break;
                    }
                }
            }

            // Deduplicate, cap at 12 images, then build a clean array for the view.
            $galleryData = $galleryData->unique(fn ($item) => $item['image']->id)->values()->take(12);

            $galleryImages = [];

            if ($galleryData->isNotEmpty()) {
                $fallbackUrl = asset('public/assets/images/safari-hero.jpg');

                foreach ($galleryData as $entry) {
                    $img = $entry['image'];
                    $parent = $entry['parent'];

                    $url = $img->getUrl('large-webp')
                        ?: $img->getUrl('medium-webp')
                        ?: $img->getUrl('medium')
                        ?: $img->getUrl()
                        ?: $fallbackUrl;

                    $alt = $img->meta->alt_text
                        ?? $img->meta->title
                        ?? $img->name
                        ?? 'Safari Experience';

                    $caption = $img->meta->description
                        ?? $img->meta->caption
                        ?? null;

                    $country = ($parent instanceof Destination)
                        ? strtoupper($parent->country_code ?: 'Tanzania')
                        : 'Tanzania';

                    $destination = ($parent instanceof Destination)
                        ? $parent->name
                        : ($parent->name ?? 'Safari');

                    // Infer gallery category from destination name keywords.
                    $catKey = strtolower($destination);
                    $category = 'Wildlife';
                    if (preg_match('/zanzibar|beach|island|coast|mnemba|pemba|mafia/i', $catKey)) {
                        $category = 'Beaches';
                    } elseif (preg_match('/kilimanjaro|meru|mount|peak|climb|trek|rim/i', $catKey)) {
                        $category = 'Mountains';
                    } elseif (preg_match('/maasai|village|culture|stone\s*town|heritage|tribal|local|dance/i', $catKey)) {
                        $category = 'Culture';
                    } elseif (preg_match('/lodge|camp|tented|resort|hotel|villa/i', $catKey)) {
                        $category = 'Lodges';
                    }

                    $galleryImages[] = [
                        'url'         => $url,
                        'alt'         => $alt,
                        'caption'     => $caption,
                        'country'     => $country,
                        'destination' => $destination,
                        'category'    => $category,
                    ];
                }
            }

            // General East-Africa safari FAQs for the homepage FAQ section.
            $faqs = collect([
                [
                    'question' => 'Why should I choose a Tanzania safari tour?',
                    'answer'   => 'Tanzania combines the Serengeti, the Ngorongoro Crater and Mount Kilimanjaro in one country, so you can pair world-class wildlife viewing with a mountain climb or a Zanzibar beach escape. Parks are long established, guides are locally trained, and routes suit both first-time and returning travellers.',
                ],
                [
                    'question' => 'Which are the best destinations for a safari?',
                    'answer'   => 'The Serengeti is famous for big cats and the Great Migration, while the Ngorongoro Crater offers exceptionally dense wildlife in a single caldera. Tarangire is known for large elephant herds and baobabs, Lake Manyara for tree-climbing lions, and the nearby coast adds Zanzibar for a relaxing finish.',
                ],
                [
                    'question' => 'What time of year is best for going on safari?',
                    'answer'   => 'The June to October dry season is the classic window: vegetation is thin, animals gather around water sources, and game drives are at their best. Calving season from January to February is superb for predators, while the March to May long rains bring lower prices and lush green landscapes.',
                ],
                [
                    'question' => 'What wildlife can I expect to see?',
                    'answer'   => 'Tanzania’s northern parks host all of the Big Five – elephant, lion, leopard, buffalo and rhino – alongside cheetahs, giraffes, zebras, wildebeest and hippos. Seasonal flamingo flocks, troops of baboons and more than 500 bird species make every drive different.',
                ],
                [
                    'question' => 'What does a typical safari day look like?',
                    'answer'   => 'Days usually start before sunrise with coffee, followed by a morning game drive when wildlife is most active. After a midday rest at your lodge or camp, you head out again for an afternoon drive or guided walk, ending with sundowners and dinner under canvas or stars.',
                ],
                [
                    'question' => 'How much will the safari cost?',
                    'answer'   => 'Costs depend on the season, comfort level, group size and the parks you visit. Budget camping safaris start from a few hundred dollars per day, while luxury lodges sit at the other end of the range – every Afro-Vertex quote itemises park fees, guide, vehicle and meals up front.',
                ],
                [
                    'question' => 'What should I consider when choosing a safari tour?',
                    'answer'   => 'Look at trip length, which parks are included, accommodation style, private versus group travel, and how demanding the itinerary is. Travelling in high season costs more but maximises wildlife sightings, and an operator with licensed local guides will always get you closer – safely.',
                ],
            ]);

            $faqExpertName = Setting::get('faq_expert_name', 'Afro-Vertex Safari Experts');
            $faqExpert = [
                'name'  => $faqExpertName,
                'image' => Setting::imageUrl('faq_expert_image_id')
                    ?: (Setting::logoUrl() ?: asset('front-end/html/assets/img/logo-1.webp')),
                'bio'   => Setting::get('faq_expert_bio',
                    'Born and raised in northern Tanzania, our safari experts have planned hundreds of trips across the Serengeti, Ngorongoro and Kilimanjaro regions. Every answer below reflects years of first-hand guiding experience.'),
                'link'  => Setting::get('faq_expert_link', route('home')),
            ];

            return view('frontend.home.index', compact(
                'featuredTours',
                'toursCount',
                'operatorReviews',
                'avgOperatorRating',
                'featuredDestinations',
                'latestPosts',
                'accommodations',
                'testimonials',
                'faqs',
                'faqExpert',
                'galleryImages'
            ))->with('fromPrices', \App\Services\TourPriceResolver::fromPriceMap($featuredTours));
        }

    /**
     * Public listing of every published accommodation, in display order.
     * Mirrors /destinations so each stay is shown as a card that opens a
     * Bootstrap detail modal.
     */
    public function accommodations(Request $request)
    {
        $accommodations = Accommodation::published()
                                       ->with('destination')
                                       ->orderBy('order')
                                       ->orderBy('name')
                                       ->paginate(12)
                                       ->withQueryString();

        return view('frontend.accommodations.index', compact('accommodations'));
    }
}

