<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->nullable()->after('email');
            $table->boolean('is_suspended')->default(false)->after('role');
            $table->boolean('must_change_password')->default(false)->after('is_suspended');
            $table->unsignedBigInteger('created_by')->nullable()->after('must_change_password');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'is_suspended', 'must_change_password', 'created_by']);
        });
    }
};