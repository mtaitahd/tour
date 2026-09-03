<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // Distinguishes a general Contact Us submission from a tour-specific
            // booking/quote request. Inferring this from tour_package_id being null
            // was possible but fragile (and ContactController was already trying to
            // set an Inquiry::create(['type' => 'contact', ...]) that silently did
            // nothing, since this column never existed) — explicit is clearer.
            $table->string('type')->default('contact')->after('tour_package_id');

            // The rest of these match the "Request a Quote" form on the tour details
            // page field-for-field (see resources/views/frontend/tours/show.blade.php)
            // — that form was collecting all of this already, it just had nowhere to
            // be saved.
            $table->string('companions')->nullable()->after('message');
            $table->string('accommodation')->nullable()->after('companions');
            $table->string('room_type')->nullable()->after('accommodation');
            $table->string('bed_type')->nullable()->after('room_type');
            $table->decimal('budget_min', 10, 2)->nullable()->after('bed_type');
            $table->decimal('budget_max', 10, 2)->nullable()->after('budget_min');
            $table->string('adult_age_range')->nullable()->after('budget_max');
            $table->string('children_age_range')->nullable()->after('adult_age_range');
            $table->string('country')->nullable()->after('children_age_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'companions', 'accommodation', 'room_type', 'bed_type',
                'budget_min', 'budget_max', 'adult_age_range', 'children_age_range',
                'country',
            ]);
        });
    }
};
