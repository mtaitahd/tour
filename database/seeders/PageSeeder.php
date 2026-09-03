<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::create([
            'slug' => 'about-us',
            'title' => 'About Afro-Vertex Tours & Safaris',
            'content' => '<p>We are a passionate team based in Moshi, offering the best safaris and Kilimanjaro climbs in East Africa.</p>',
            'is_published' => true,
            'order' => 10,
        ]);

        Page::create([
            'slug' => 'contact',
            'title' => 'Contact Us',
            'content' => '<p>Reach us via WhatsApp or email. Location: Moshi, Kilimanjaro.</p>',
            'is_published' => true,
            'order' => 20,
        ]);
    }
}
