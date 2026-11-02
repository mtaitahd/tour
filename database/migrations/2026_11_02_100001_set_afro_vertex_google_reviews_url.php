<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::set('google_reviews_url', 'https://share.google/QJW9rNYeE8H0WHB3c');
    }

    public function down(): void
    {
        // Preserve this editable setting, including any later administrator changes.
    }
};
