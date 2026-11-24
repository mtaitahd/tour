<?php

namespace App\Support;

use App\Models\Setting;

class LandingDefaults
{
    public static function rows(string $key): array
    {
        $defaults = [
'footer_links_park' => [
        ['label' => 'Maasai Mara Game Reserve Safaris', 'url' => '/maasai-mara'],
        ['label' => 'Mount Kilimanjaro Treks',          'url' => '/mount-kilimanjaro'],
        ['label' => 'Serengeti National Park Safaris', 'url' => '/serengeti-national-park'],
    ],
'footer_links_country' => [
        ['label' => 'Tanzania Safaris', 'url' => '/destinations?country=TZ'],
        ['label' => 'Kenya Safaris',    'url' => '/destinations?country=KE'],
    ],
'footer_links_type' => [
        ['label' => 'Kilimanjaro Climbing', 'url' => '/kilimanjaro-climbing-package'],
        ['label' => 'Tanzania Tours',       'url' => '/tanzania-tours'],
    ],
'footer_links_general' => [
        ['label' => 'About Us',             'url' => '/pages/about-us'],
        ['label' => 'Why Choose Us',        'url' => route('home') . '#why-choose-us'],
        ['label' => 'Contact Us',           'url' => '/pages/contact'],
        ['label' => 'Blog',                 'url' => '/blog'],
        ['label' => 'Terms and Conditions', 'url' => '/pages/terms-and-conditions'],
    ],
'home_about_polaroids' => [
                            ['src' => asset('public/111/serengeti-great-migration.webp'), 'caption' => Setting::get('home_about_polaroid_1') ?: 'Wild Encounters'],
                            ['src' => asset('public/114/serengeti-elephants.webp'), 'caption' => Setting::get('home_about_polaroid_2') ?: 'Memories Forever'],
                            ['src' => asset('public/112/serengeti-lions-1.webp'), 'caption' => Setting::get('home_about_polaroid_3') ?: 'Breathtaking Views'],
                        ],
'subscribe_videos' => [
        [
            'thumbnail' => asset('public/safari-countries/tanzania.webp'),
            'title'     => 'The Great Migration — Serengeti Up Close',
            'views'     => '12K views',
            'when'      => '2 weeks ago',
            'url'       => (\App\Models\Setting::get('subscribe_youtube_url') ?: \App\Models\Setting::get('social_youtube')) ?: '#',
        ],
        [
            'thumbnail' => asset('public/safari-countries/tanzania.webp'),
            'title'     => 'Climbing Mount Kilimanjaro — Machame Route',
            'views'     => '8.4K views',
            'when'      => '1 month ago',
            'url'       => (\App\Models\Setting::get('subscribe_youtube_url') ?: \App\Models\Setting::get('social_youtube')) ?: '#',
        ],
        [
            'thumbnail' => asset('public/safari-countries/botswana.webp'),
            'title'     => 'Ngorongoro Crater — A Day in the Caldera',
            'views'     => '6.1K views',
            'when'      => '2 months ago',
            'url'       => (\App\Models\Setting::get('subscribe_youtube_url') ?: \App\Models\Setting::get('social_youtube')) ?: '#',
        ],
    ],
'atlas' => [
            [
                'id'=>'tanzania','rank'=>1,'name'=>'Tanzania','slug'=>'tanzania',
                'rating'=>4.9,'reviewCount'=>'2,847',
                'description'=>"Home to the Serengeti, Ngorongoro Crater, Mount Kilimanjaro and the islands of Zanzibar, Tanzania offers an extraordinary range of adventures. Witness the Great Migration, explore wildlife-rich national parks with professional local guides, climb Africa's highest mountain or finish your journey beside the Indian Ocean.",
                'priceRange'=>'From $238 per person/day','bestTime'=>'June to October & January to February','highSeason'=>'July to October',
                'heroImage'=>asset('public/safari-countries/tanzania.webp'),
                'heroImageAlt'=>'Serengeti Great Migration with wildebeest herds crossing the savannah',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','tanzania'),
                'tourCount'=>124,
                'destinations'=>[
                    ['name'=>'Serengeti','cat'=>'Wildlife'],
                    ['name'=>'Ngorongoro','cat'=>'Crater'],
                    ['name'=>'Zanzibar','cat'=>'Beach'],
                ],
            ],
            [
                'id'=>'kenya','rank'=>2,'name'=>'Kenya','slug'=>'kenya',
                'rating'=>4.8,'reviewCount'=>'2,341',
                'description'=>"Kenya is the birthplace of the safari experience. From the vast plains of the Maasai Mara to the flamingo-lined shores of Lake Nakuru and the snow-capped peak of Mount Kenya, this iconic destination delivers classic African wildlife encounters combined with vibrant cultural heritage.",
                'priceRange'=>'From $215 per person/day','bestTime'=>'July to October & January to February','highSeason'=>'July to October',
                'heroImage'=>asset('public/safari-countries/kenya.webp'),
                'heroImageAlt'=>'Maasai Mara savannah landscape with acacia trees at golden hour',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','kenya'),
                'tourCount'=>108,
                'destinations'=>[
                    ['name'=>'Maasai Mara','cat'=>'Wildlife'],
                    ['name'=>'Amboseli','cat'=>'Safari'],
                    ['name'=>'Diani Beach','cat'=>'Beach'],
                ],
            ],
            [
                'id'=>'uganda','rank'=>3,'name'=>'Uganda','slug'=>'uganda',
                'rating'=>4.7,'reviewCount'=>'1,658',
                'description'=>"Known as the Pearl of Africa, Uganda is home to endangered mountain gorillas in Bwindi Impenetrable Forest, chimpanzees in Kibale and the powerful Murchison Falls. Combine primate trekking with savannah safaris, water sports on Lake Victoria and lush crater-lake scenery.",
                'priceRange'=>'From $265 per person/day','bestTime'=>'June to September & December to February','highSeason'=>'June to September',
                'heroImage'=>asset('public/safari-countries/uganda.jpg'),
                'heroImageAlt'=>'Mountain gorilla in Bwindi Impenetrable Forest Uganda',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','uganda'),
                'tourCount'=>73,
                'destinations'=>[
                    ['name'=>'Bwindi','cat'=>'Gorillas'],
                    ['name'=>'Murchison Falls','cat'=>'Adventure'],
                    ['name'=>'Jinja','cat'=>'Water'],
                ],
            ],
            [
                'id'=>'rwanda','rank'=>4,'name'=>'Rwanda','slug'=>'rwanda',
                'rating'=>4.8,'reviewCount'=>'1,203',
                'description'=>"Rwanda offers intimate gorilla trekking experiences in Volcanoes National Park alongside golden monkey encounters and the dramatic canopy walk in Nyungwe Forest. The Land of a Thousand Hills also features Akagera's savannah Big Five and the tranquil shores of Lake Kivu.",
                'priceRange'=>'From $310 per person/day','bestTime'=>'June to September & December to February','highSeason'=>'June to September',
                'heroImage'=>asset('public/safari-countries/rwanda.jpg'),
                'heroImageAlt'=>'Gorilla trekking experience in Volcanoes National Park Rwanda',
                'safariPageUrl'=>route('tours.index'),'countryPageUrl'=>route('destination.show','rwanda'),
                'tourCount'=>41,
                'destinations'=>[
                    ['name'=>'Volcanoes NP','cat'=>'Gorillas'],
                    ['name'=>'Nyungwe','cat'=>'Forest'],
                    ['name'=>'Akagera','cat'=>'Savannah'],
                ],
            ],
        ],
'faq' => [
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
            ]
        ];

        return $defaults[$key] ?? [];
    }
}
