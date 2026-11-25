<?php

return array (
  'seo' =>
  array (
    'title' => 'Homepage SEO',
    'fields' =>
    array (
      'home_meta_title' =>
      array (
        'label' => 'SEO Title',
        'type' => 'text',
        'default' => '',
        'max' => 255,
        'hint' => 'The title shown in the browser tab and supplied to search engines.',
      ),
      'meta_description' =>
      array (
        'label' => 'Meta Description',
        'type' => 'text',
        'default' => 'Discover Tanzania safaris, Kilimanjaro climbs, Zanzibar beaches and gorilla trekking with Afro-Vertex Tours.',
        'blank_uses_default' => true,
        'max' => 1000,
        'hint' => 'A short summary of the homepage for search engines and link previews.',
      ),
    ),
  ),
  'hero' =>
  array (
    'title' => 'Hero',
    'fields' =>
    array (
      'hero_image_id' =>
      array (
        'label' => 'Hero Image',
        'type' => 'image',
        'default' => '',
      ),
      'hero_title' =>
      array (
        'label' => 'Hero Title',
        'type' => 'text',
        'default' => 'Discover the Wild Beauty of East Africa',
      ),
      'hero_subtitle' =>
      array (
        'label' => 'Hero Subtitle',
        'type' => 'text',
        'default' => 'Handcrafted safaris, mountain climbs, and beach escapes — curated by local specialists who know Africa best.',
      ),
    ),
  ),
  'about' =>
  array (
    'title' => 'Our Story',
    'fields' =>
    array (
      'home_about_eyebrow' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Eyebrow',
        'type' => 'text',
        'default' => 'Our Story',
      ),
      'home_about_title' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Title',
        'type' => 'text',
        'default' => 'About Us &ndash; Afro-Vertex Tours &amp; Safaris',
      ),
      'home_about_text' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Text',
        'type' => 'text',
        'default' => 'Welcome to Afro-Vertex Africa Tanzania Safari LTD, where unforgettable African adventures meet the untamed beauty of nature. Based in Tanzania, we are a trusted safari operator and destination management company dedicated to creating immersive wildlife experiences, tailor-made journeys, mountain adventures, and relaxing beach escapes across East Africa.',
      ),
      'home_about_checklist' =>
      array (
        'label' => 'About Checklist',
        'type' => 'text',
        'default' => 'Custom Safari Itineraries
24/7 Customer Support
Licensed & Insured
Professional Local Guides
Best Price Guarantee
Sustainable Tourism',
      ),
      'home_about_btn_link' =>
      array (
        'label' => 'About Button Link',
        'type' => 'text',
        'default' => '/pages/about-us',
      ),
      'home_about_btn_text' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Button Text',
        'type' => 'text',
        'default' => 'Discover More',
      ),
      'home_about_polaroids' =>
      array (
        'label' => 'About Polaroids',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'photo_image_id',
          1 => 'src',
          2 => 'caption',
        ),
      ),
      'home_about_polaroid_1' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Polaroid 1',
        'type' => 'text',
        'default' => 'Wild Encounters',
      ),
      'home_about_polaroid_2' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Polaroid 2',
        'type' => 'text',
        'default' => 'Memories Forever',
      ),
      'home_about_polaroid_3' =>
      array (
        'blank_uses_default' => true,
        'label' => 'About Polaroid 3',
        'type' => 'text',
        'default' => 'Breathtaking Views',
      ),
    ),
  ),
  'atlas' =>
  array (
    'title' => 'Safari Atlas Explorer',
    'fields' =>
    array (
      'landing_sae_eyebrow_1' =>
      array (
        'label' => 'Eyebrow 1',
        'type' => 'text',
        'default' => 'Explore Africa',
      ),
      'landing_sae_title_1' =>
      array (
        'label' => 'Title 1',
        'type' => 'text',
        'default' => 'Find Your Wild Side',
      ),
      'landing_sae_subtitle_1' =>
      array (
        'label' => 'Subtitle 1',
        'type' => 'text',
        'default' => 'Four remarkable countries. Four completely different ways to experience Africa.',
      ),
      'landing_atlas_countries' =>
      array (
        'label' => 'Country details',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'id',
          1 => 'name',
          2 => 'continentLabel',
          3 => 'description',
          4 => 'priceRange',
          5 => 'bestTime',
          6 => 'highSeason',
          7 => 'photo_image_id',
          8 => 'heroImage',
          9 => 'heroImageAlt',
          10 => 'primaryLabel',
          11 => 'safariPageUrl',
          12 => 'secondaryLabel',
          13 => 'countryPageUrl',
        ),
        'defaults' => 'atlas',
      ),
    ),
  ),
  'destinations' =>
  array (
    'title' => 'Featured Destinations',
    'fields' =>
    array (
      'home_sec_destinations_title' =>
      array (
        'label' => 'Destinations Title',
        'type' => 'text',
        'default' => 'Explore Top Destinations.',
      ),
      'home_sec_destinations_subtitle' =>
      array (
        'label' => 'Destinations Subtitle',
        'type' => 'text',
        'default' => 'Diverse landscapes, iconic wildlife, and cultures that stay with you long after you return home.',
      ),
      'home_btn_destinations' =>
      array (
        'label' => 'Btn Destinations',
        'type' => 'text',
        'default' => 'View All Destinations',
      ),
    ),
    'manage' => 'admin.destinations.index',
  ),
  'tours' =>
  array (
    'title' => 'Featured Tours',
    'fields' =>
    array (
      'home_sec_tours_title' =>
      array (
        'label' => 'Tours Title',
        'type' => 'text',
        'default' => 'Featured Tours Around East Africa.',
      ),
      'home_sec_tours_subtitle' =>
      array (
        'label' => 'Tours Subtitle',
        'type' => 'text',
        'default' => 'Handpicked itineraries across Tanzania, Kenya, Uganda, and Rwanda — designed by locals who know every trail and watering hole.',
      ),
      'home_btn_tours' =>
      array (
        'label' => 'Btn Tours',
        'type' => 'text',
        'default' => 'Explore All {count} Safari Tours',
      ),
    ),
    'manage' => 'admin.tour-packages.index',
  ),
  'countries' =>
  array (
    'title' => 'Worldwide Group Travellers',
    'fields' =>
    array (
      'home_sec_countries_eyebrow' =>
      array (
        'label' => 'Countries Eyebrow',
        'type' => 'text',
        'default' => 'Worldwide Group Travellers',
      ),
      'home_sec_countries_title' =>
      array (
        'label' => 'Countries Title',
        'type' => 'text',
        'default' => 'Kilimanjaro Group Tours for Adventurers from Every Country',
      ),
      'home_sec_countries_subtitle' =>
      array (
        'label' => 'Countries Subtitle',
        'type' => 'text',
        'default' => 'Our group tours bring together climbers and travellers from around the world. Choose your country to explore relevant travel information and start planning your Kilimanjaro adventure.',
      ),
      'home_btn_countries_more' =>
      array (
        'label' => 'Btn Countries More',
        'type' => 'text',
        'default' => 'View All Countries',
      ),
      'home_btn_countries_less' =>
      array (
        'label' => 'Btn Countries Less',
        'type' => 'text',
        'default' => 'Show Fewer Countries',
      ),
      'landing_country_links' =>
      array (
        'label' => 'Country links',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'code',
          1 => 'name',
          2 => 'url',
        ),
      ),
    ),
    'manage' => 'admin.pages.index',
  ),
  'how-it-works' =>
  array (
    'title' => 'How It Works',
    'fields' =>
    array (
      'landing_hiw_eyebrow_text_1' =>
      array (
        'label' => 'Eyebrow Text 1',
        'type' => 'text',
        'default' => 'How It Works',
      ),
      'landing_hiw_title_1' =>
      array (
        'label' => 'Title 1',
        'type' => 'text',
        'default' => 'Your Safari, Made Simple',
      ),
      'landing_hiw_desc_1' =>
      array (
        'label' => 'Desc 1',
        'type' => 'text',
        'default' => 'Three easy steps from dream trip to departure day — we handle the details so you can focus on the adventure.',
      ),
      'landing_hiw_card_title_1' =>
      array (
        'label' => 'Card Title 1',
        'type' => 'text',
        'default' => 'Find Your Tour',
      ),
      'landing_hiw_card_desc_1' =>
      array (
        'label' => 'Card Description 1',
        'type' => 'text',
        'default' => 'Explore handpicked safaris or search for the adventure that fits you.',
      ),
      'landing_hiw_card_title_2' =>
      array (
        'label' => 'Card Title 2',
        'type' => 'text',
        'default' => 'Book & Confirm',
      ),
      'landing_hiw_card_desc_2' =>
      array (
        'label' => 'Card Description 2',
        'type' => 'text',
        'default' => 'Choose your dates and travelers, then reserve your place with confidence.',
      ),
      'landing_hiw_card_title_3' =>
      array (
        'label' => 'Card Title 3',
        'type' => 'text',
        'default' => 'Enjoy the Journey',
      ),
      'landing_hiw_card_desc_3' =>
      array (
        'label' => 'Card Description 3',
        'type' => 'text',
        'default' => 'Meet your guide, explore East Africa, and create lasting memories.',
      ),
    ),
  ),
  'accommodations' =>
  array (
    'title' => 'Accommodations',
    'fields' =>
    array (
      'home_sec_accommodations_eyebrow' =>
      array (
        'label' => 'Accommodations Eyebrow',
        'type' => 'text',
        'default' => 'Where You\'ll Stay',
      ),
      'home_sec_accommodations_title' =>
      array (
        'label' => 'Accommodations Title',
        'type' => 'text',
        'default' => 'Relaxing Accommodations.',
      ),
      'home_sec_accommodations_subtitle' =>
      array (
        'label' => 'Accommodations Subtitle',
        'type' => 'text',
        'default' => 'Boutique lodges, tented camps, and beach resorts — each stay handpicked for comfort, character, and a front-row seat to the wild.',
      ),
      'home_btn_accommodations' =>
      array (
        'label' => 'Btn Accommodations',
        'type' => 'text',
        'default' => 'View All Accommodations',
      ),
    ),
    'manage' => 'admin.accommodations.index',
  ),
  'why-choose-us' =>
  array (
    'title' => 'Why Choose Us',
    'fields' =>
    array (
      'why_choose_us_cards' =>
      array (
        'label' => 'Why Choose Us Cards',
        'type' => 'list',
        'default' => '[]',
        'columns' =>
        array (
          0 => 'icon',
          1 => 'title',
          2 => 'description',
        ),
      ),
      'landing_why_eyebrow' =>
      array (
        'label' => 'Eyebrow',
        'type' => 'text',
        'default' => 'Why Choose Us',
      ),
      'landing_why_title' =>
      array (
        'label' => 'Title',
        'type' => 'text',
        'default' => 'The Local Specialists You Can Trust',
      ),
      'landing_why_subtitle' =>
      array (
        'label' => 'Subtitle',
        'type' => 'text',
        'default' => 'Over a decade of on-the-ground experience, trusted partnerships, and a genuine love for the places we call home.',
      ),
    ),
  ),
  'blog' =>
  array (
    'title' => 'Latest Blog Posts',
    'fields' =>
    array (
      'home_sec_blog_title' =>
      array (
        'label' => 'Blog Title',
        'type' => 'text',
        'default' => 'Latest Blog Posts.',
      ),
      'home_sec_blog_subtitle' =>
      array (
        'label' => 'Blog Subtitle',
        'type' => 'text',
        'default' => 'Stories, tips, and field notes from the road — everything you need before your next African adventure.',
      ),
      'home_btn_blog' =>
      array (
        'label' => 'Btn Blog',
        'type' => 'text',
        'default' => 'All Blog Posts',
      ),
    ),
    'manage' => 'admin.blog-posts.index',
  ),
  'gallery' =>
  array (
    'title' => 'Photo Gallery',
    'fields' =>
    array (
      'home_sec_gallery_title' =>
      array (
        'label' => 'Gallery Title',
        'type' => 'text',
        'default' => 'The Afro Gallery',
      ),
      'home_sec_gallery_subtitle' =>
      array (
        'label' => 'Gallery Subtitle',
        'type' => 'text',
        'default' => 'Raw beauty, untold stories, and landscapes that demand to be explored — curated from our travels across the continent.',
      ),
      'home_sec_gallery_eyebrow' =>
      array (
        'label' => 'Gallery Eyebrow',
        'type' => 'text',
        'default' => 'The Untamed Archive',
      ),
      'landing_gallery_photos' =>
      array (
        'label' => 'Gallery photos (leave empty to use destination and tour photos)',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'photo_image_id',
          1 => 'alt',
          2 => 'caption',
          3 => 'country',
          4 => 'destination',
          5 => 'category',
        ),
      ),
    ),
    'manage' => 'admin.media.index',
  ),
  'reviews' =>
  array (
    'title' => 'Traveller Stories',
    'fields' =>
    array (
      'traveller_badge' =>
      array (
        'label' => 'Traveller Badge',
        'type' => 'text',
        'default' => 'Traveller Stories',
      ),
      'traveller_title' =>
      array (
        'label' => 'Traveller Title',
        'type' => 'text',
        'default' => 'Our Latest Customer Reviews',
      ),
      'traveller_map_embed' =>
      array (
        'label' => 'Traveller Map Embed',
        'type' => 'text',
        'default' => '',
      ),
      'traveller_tripadvisor_logo_id' =>
      array (
        'label' => 'Traveller Tripadvisor Logo',
        'type' => 'image',
        'default' => '',
      ),
      'traveller_map_heading' =>
      array (
        'label' => 'Traveller Map Heading',
        'type' => 'text',
        'default' => 'Where We Are',
      ),
      'traveller_tripadvisor_heading' =>
      array (
        'label' => 'Traveller Tripadvisor Heading',
        'type' => 'text',
        'default' => 'Loved by Travellers on Tripadvisor',
      ),
      'traveller_tripadvisor_text' =>
      array (
        'label' => 'Traveller Tripadvisor Text',
        'type' => 'text',
        'default' => '',
      ),
      'traveller_tripadvisor_link' =>
      array (
        'label' => 'Traveller Tripadvisor Link',
        'type' => 'text',
        'default' => '',
      ),
      'traveller_tripadvisor_button_text' =>
      array (
        'label' => 'Traveller Tripadvisor Button Text',
        'type' => 'text',
        'default' => 'Read Tripadvisor Reviews',
      ),
      'traveller_review_link' =>
      array (
        'label' => 'Traveller Review Link',
        'type' => 'text',
        'default' => 'https://g.page/r/CR7qe8CBnNH7EBM/review',
      ),
      'traveller_review_link_text' =>
      array (
        'label' => 'Traveller Review Link Text',
        'type' => 'text',
        'default' => 'Leave a Review',
      ),
    ),
  ),
  'cta' =>
  array (
    'title' => 'Plan Your Safari',
    'fields' =>
    array (
      'cta_title' =>
      array (
        'label' => 'Title',
        'type' => 'text',
        'default' => 'Your Tour in Africa, Our Passion!',
      ),
      'cta_subtitle' =>
      array (
        'label' => 'Subtitle',
        'type' => 'text',
        'default' => 'Planning an Africa tour can be exciting, but we know you might have questions. Whether you are curious about the best time to visit, what to pack, or which safari suits your style, our local specialist team is ready to guide you.',
      ),
      'cta_button_link' =>
      array (
        'label' => 'Button Link',
        'type' => 'text',
        'default' => '/contact',
      ),
      'cta_button_text' =>
      array (
        'label' => 'Button Text',
        'type' => 'text',
        'default' => 'Plan My Safari',
      ),
      'cta_image' =>
      array (
        'label' => 'Image',
        'type' => 'text',
        'default' => '',
      ),
    ),
  ),
  'faq' =>
  array (
    'title' => 'Frequently Asked Questions',
    'fields' =>
    array (
      'faq_expert_name' =>
      array (
        'label' => 'FAQ Expert Name',
        'type' => 'text',
        'default' => 'Afro-Vertex Local Specialists',
      ),
      'faq_expert_bio' =>
      array (
        'label' => 'FAQ Expert Bio',
        'type' => 'text',
        'default' => 'Born and raised in northern Tanzania, our local specialists have planned hundreds of trips across the Serengeti, Ngorongoro and Kilimanjaro regions. Every answer below reflects years of first-hand guiding experience.',
      ),
      'faq_expert_link' =>
      array (
        'label' => 'FAQ Expert Link',
        'type' => 'text',
        'default' => '/',
      ),
      'faq_expert_image_id' =>
      array (
        'label' => 'FAQ Expert Image',
        'type' => 'image',
        'default' => '',
      ),
      'landing_home_faqs' =>
      array (
        'label' => 'Questions and answers',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'question',
          1 => 'answer',
        ),
        'defaults' => 'faq',
      ),
    ),
  ),
  'subscribe' =>
  array (
    'title' => 'YouTube Subscribe',
    'fields' =>
    array (
      'subscribe_youtube_url' =>
      array (
        'label' => 'Subscribe Youtube Url',
        'type' => 'text',
        'default' => '',
      ),
      'social_youtube' =>
      array (
        'label' => 'Social Youtube',
        'type' => 'text',
        'default' => '',
      ),
      'subscribe_videos' =>
      array (
        'label' => 'Videos',
        'type' => 'list',
        'columns' =>
        array (
          0 => 'thumbnail',
          1 => 'title',
          2 => 'views',
          3 => 'when',
          4 => 'url',
        ),
        'default' => '',
      ),
      'subscribe_heading' =>
      array (
        'label' => 'Heading',
        'type' => 'text',
        'default' => 'Subscribe Afro&#8209;Vertex Tours &amp; Safaris on YouTube',
      ),
      'subscribe_hidden' =>
      array (
        'label' => 'Hide YouTube section',
        'type' => 'checkbox',
        'default' => '0',
      ),
    ),
  ),
  'footer' =>
  array (
    'title' => 'Footer',
    'fields' =>
    array (
      'site_name' =>
      array (
        'label' => 'Site Name',
        'type' => 'text',
        'default' => 'Afro-Vertex Tours & Safaris',
      ),
      'footer_about_heading' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer About Heading',
        'type' => 'text',
        'default' => 'About ',
      ),
      'footer_about_text' =>
      array (
        'label' => 'Footer About Text',
        'type' => 'text',
        'default' => '',
      ),
      'footer_about_link_text' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer About Link Text',
        'type' => 'text',
        'default' => 'More About Us',
      ),
      'footer_heading_parks' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Heading Parks',
        'type' => 'text',
        'default' => 'Safaris by Park',
      ),
      'footer_heading_countries' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Heading Countries',
        'type' => 'text',
        'default' => 'Safaris by Country',
      ),
      'footer_heading_types' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Heading Types',
        'type' => 'text',
        'default' => 'Safaris by Type',
      ),
      'footer_heading_general' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Heading General',
        'type' => 'text',
        'default' => 'General',
      ),
      'footer_heading_partners' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Heading Partners',
        'type' => 'text',
        'default' => 'Our Partners',
      ),
      'footer_empty_parks' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Empty Parks',
        'type' => 'text',
        'default' => 'Coming soon.',
      ),
      'footer_empty_general' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Empty General',
        'type' => 'text',
        'default' => 'Coming soon.',
      ),
      'footer_copy_privacy' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Copy Privacy',
        'type' => 'text',
        'default' => 'Privacy Policy',
      ),
      'footer_copy_terms' =>
      array (
        'blank_uses_default' => true,
        'label' => 'Footer Copy Terms',
        'type' => 'text',
        'default' => 'Terms & Conditions',
      ),
      'footer_links_park' =>
      array (
        'label' => 'Footer Links Park',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'label',
          1 => 'url',
        ),
      ),
      'footer_links_country' =>
      array (
        'label' => 'Footer Links Country',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'label',
          1 => 'url',
        ),
      ),
      'footer_links_type' =>
      array (
        'label' => 'Footer Links Type',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'label',
          1 => 'url',
        ),
      ),
      'footer_links_general' =>
      array (
        'label' => 'Footer Links General',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'label',
          1 => 'url',
        ),
      ),
      'social_facebook' =>
      array (
        'label' => 'Social Facebook',
        'type' => 'text',
        'default' => '',
      ),
      'social_instagram' =>
      array (
        'label' => 'Social Instagram',
        'type' => 'text',
        'default' => '',
      ),
      'social_youtube' =>
      array (
        'label' => 'Social Youtube',
        'type' => 'text',
        'default' => '',
      ),
      'social_twitter' =>
      array (
        'label' => 'Social Twitter',
        'type' => 'text',
        'default' => '',
      ),
      'social_linkedin' =>
      array (
        'label' => 'Social Linkedin',
        'type' => 'text',
        'default' => '',
      ),
      'partner_logos' =>
      array (
        'label' => 'Partner Logos',
        'type' => 'list',
        'default' => '',
        'columns' =>
        array (
          0 => 'name',
          1 => 'link',
          2 => 'logo_image_id',
        ),
      ),
      'landing_footer_copyright' =>
      array (
        'label' => 'Copyright text',
        'type' => 'text',
        'default' => 'All Rights Reserved.',
      ),
      'logo_image_id' =>
      array (
        'label' => 'Logo',
        'type' => 'image',
        'default' => '',
      ),
    ),
  ),
);
