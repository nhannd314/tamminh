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
        Schema::table('experts', function (Blueprint $table) {
            $table->string('title')->nullable()->after('glyph');
            $table->string('headline')->nullable()->after('title');
            $table->boolean('is_accepting_bookings')->default(true)->after('is_active');
            $table->string('working_hours')->nullable()->after('address');
            $table->string('office_image_path')->nullable()->after('working_hours');
            $table->decimal('rating', 2, 1)->nullable()->after('sessions_count');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating');
            $table->json('specialties')->nullable()->after('description');
            $table->json('services')->nullable()->after('specialties');
            $table->json('reviews')->nullable()->after('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'headline', 'is_accepting_bookings', 'working_hours', 'office_image_path',
                'rating', 'reviews_count', 'specialties', 'services', 'reviews',
            ]);
        });
    }
};
