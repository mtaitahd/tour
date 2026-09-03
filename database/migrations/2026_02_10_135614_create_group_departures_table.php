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
        Schema::create('group_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->onDelete('cascade');
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('available_spots')->default(12);
            $table->integer('total_spots')->default(12);
            $table->decimal('group_price', 10, 2)->nullable(); // optional override
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['open', 'guaranteed', 'limited', 'sold_out', 'cancelled'])->default('open');
            $table->boolean('is_featured')->default(false);
            $table->text('notes')->nullable(); // internal admin notes
            $table->timestamps();

            $table->index(['tour_package_id', 'departure_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_departures');
    }
};
