<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Afro-Vertex Tours & Safaris',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name',
            ],
            [
                'key' => 'footer_copyright',
                'value' => '© ' . date('Y') . ' Afro-Vertex Tours & Safaris. All Rights Reserved.',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Footer Copyright Text',
            ],
            [
                'key' => 'footer_address',
                'value' => 'Arusha, Tanzania',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Address',
            ],
            [
                'key' => 'footer_phone',
                'value' => '+255 712 345 678',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Footer Phone Number',
            ],
            [
                'key' => 'default_currency',
                'value' => 'USD',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Default Currency Code',
            ],
            [
                'key' => 'timezone',
                'value' => 'Africa/Dar_es_Salaam',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Timezone',
            ],

            // Social Links
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/afrovertextours',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Facebook URL',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/afrovertextours',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Instagram URL',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/afrovertex',
                'type' => 'url',
                'group' => 'social',
                'label' => 'X / Twitter URL',
            ],
            [
                'key' => 'social_youtube',
                'value' => '',
                'type' => 'url',
                'group' => 'social',
                'label' => 'YouTube URL',
            ],
            [
                'key' => 'social_linkedin',
                'value' => '',
                'type' => 'url',
                'group' => 'social',
                'label' => 'LinkedIn URL',
            ],

            // SEO & Advanced
            [
                'key' => 'robots_txt_content',
                'value' => "User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: " . url('/sitemap.xml'),
                'type' => 'textarea',
                'group' => 'seo',
                'label' => 'robots.txt Content',
            ],
            [
                'key' => 'enable_sitemap',
                'value' => '1',
                'type' => 'checkbox',
                'group' => 'seo',
                'label' => 'Enable Automatic Sitemap.xml',
            ],

            // Tours Listing Page Specific
            [
                'key' => 'tours_listing_intro',
                'value' => '<h2 class="text-center mb-4">Discover East Africa\'s Finest Adventures</h2>
                            <p class="lead text-center mb-5">Welcome to Afro-Vertex Tours & Safaris — your gateway to unforgettable journeys across Tanzania, Kenya, Uganda, Rwanda, and beyond. Whether you\'re dreaming of the Great Migration in Serengeti, summiting Kilimanjaro, relaxing on Zanzibar\'s white-sand beaches, or trekking to see mountain gorillas in Bwindi, we have the perfect safari, climb, or beach escape waiting for you.</p>',
                'type' => 'textarea',
                'group' => 'tours_listing',
                'label' => 'Intro Paragraph (appears above filters)',
                'description' => 'Main welcome text shown at the top of the /tours page',
            ],
            [
                'key' => 'tours_listing_sections',
                'value' => json_encode([
                    [
                        'image' => '', // client will fill this later
                        'title' => 'Why Choose Group Departures?',
                        'content' => '<p>Join like-minded travelers on fixed-date departures with guaranteed guides, shared costs, and a fun group atmosphere. Perfect for solo travelers or friends looking for structure and savings.</p>
                                      <ul class="list-unstyled">
                                          <li><i class="bi bi-check-circle-fill text-success me-2"></i>Expert local guides</li>
                                          <li><i class="bi bi-check-circle-fill text-success me-2"></i>Best rates for groups</li>
                                          <li><i class="bi bi-check-circle-fill text-success me-2"></i>Pre-planned itineraries</li>
                                      </ul>',
                        'image_side' => 'left',
                    ],
                    [
                        'image' => '',
                        'title' => 'Ready for Your Next Adventure?',
                        'content' => '<p>Use the filters below to find your ideal tour by duration, price, destination, activity, or difficulty level. Can\'t find exactly what you\'re looking for? We specialize in custom private safaris and group departures too — just send us an inquiry!</p>
                                      <a href="#filters" class="btn btn-primary mt-2">Browse All Tours Now</a>',
                        'image_side' => 'right',
                    ],
                ]),
                'type' => 'json',
                'group' => 'tours_listing',
                'label' => 'Extra Sections (below listings)',
                'description' => 'JSON array of sections with image, title, content, and image_side (left/right)',
            ],
        ];

        foreach ($settings as $data) {
            Setting::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}