<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use Illuminate\Support\Str;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            [
                'name'        => 'Wildlife Safari',
                'slug'        => Str::slug('Wildlife Safari'),
                'description' => 'Game drives, walking safaris, and big five spotting in national parks like Serengeti, Tarangire, and Masai Mara.',
                'icon'        => 'isax isax-tree',           // or 'fa-solid fa-paw' if using FontAwesome
                'order'       => 10,
                'is_featured' => true,
            ],
            [
                'name'        => 'Mountain Climbing & Trekking',
                'slug'        => Str::slug('Mountain Climbing & Trekking'),
                'description' => 'Summit attempts on Kilimanjaro, Mount Meru, Mount Kenya, and multi-day treks in the Rwenzori Mountains.',
                'icon'        => 'isax isax-mountain',
                'order'       => 20,
                'is_featured' => true,
            ],
            [
                'name'        => 'Beach Holidays & Island Escapes',
                'slug'        => Str::slug('Beach Holidays & Island Escapes'),
                'description' => 'Relaxation on Zanzibar beaches, Mafia Island, Pemba, Diani Beach (Kenya), and spice tours.',
                'icon'        => 'isax isax-sun-1',
                'order'       => 30,
                'is_featured' => true,
            ],
            [
                'name'        => 'Cultural & Village Tours',
                'slug'        => Str::slug('Cultural & Village Tours'),
                'description' => 'Maasai village visits, Hadzabe bushmen encounters, Chagga culture, Swahili heritage in Stone Town.',
                'icon'        => 'isax isax-profile-2user',
                'order'       => 40,
                'is_featured' => false,
            ],
            [
                'name'        => 'Gorilla, Chimp & Primate Trekking',
                'slug'        => Str::slug('Gorilla, Chimp & Primate Trekking'),
                'description' => 'Mountain gorilla tracking in Bwindi (Uganda) and Volcanoes National Park (Rwanda), chimpanzee trekking in Mahale & Gombe.',
                'icon'        => 'isax isax-monkey',
                'order'       => 50,
                'is_featured' => true,
            ],
            [
                'name'        => 'Bird Watching',
                'slug'        => Str::slug('Bird Watching'),
                'description' => 'Over 1,000 bird species in Serengeti, Ngorongoro, Lake Manyara, and Rubondo Island.',
                'icon'        => 'isax isax-bird',
                'order'       => 60,
                'is_featured' => false,
            ],
            [
                'name'        => 'Hot Air Balloon Safari',
                'slug'        => Str::slug('Hot Air Balloon Safari'),
                'description' => 'Sunrise balloon rides over Serengeti plains and Masai Mara during migration season.',
                'icon'        => 'isax isax-airplane',
                'order'       => 70,
                'is_featured' => true,
            ],
            [
                'name'        => 'Diving & Snorkeling',
                'slug'        => Str::slug('Diving & Snorkeling'),
                'description' => 'World-class scuba diving and snorkeling in Zanzibar, Mafia Island Marine Park, and Pemba.',
                'icon'        => 'isax isax-scuba-diving',
                'order'       => 80,
                'is_featured' => false,
            ],
            [
                'name'        => 'Wellness & Spa Retreats',
                'slug'        => Str::slug('Wellness & Spa Retreats'),
                'description' => 'Relaxing spa experiences, yoga retreats, and wellness escapes in Zanzibar and Arusha.',
                'icon'        => 'isax isax-health',
                'order'       => 90,
                'is_featured' => false,
            ],
            [
                'name'        => 'Photography Tours',
                'slug'        => Str::slug('Photography Tours'),
                'description' => 'Guided wildlife and landscape photography safaris with professional tips and best light timing.',
                'icon'        => 'isax isax-camera',
                'order'       => 100,
                'is_featured' => false,
            ],
        ];

        foreach ($activities as $activity) {
            Activity::updateOrCreate(
                ['slug' => $activity['slug']],
                $activity
            );
        }

        $this->command->info('Activities seeded successfully! (' . count($activities) . ' records)');
    }
}