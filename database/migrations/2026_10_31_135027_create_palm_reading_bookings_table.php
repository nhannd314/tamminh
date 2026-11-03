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
        Schema::create('palm_reading_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('gender', 16);
            $table->date('birth_date');
            $table->string('phone', 32);
            $table->string('package', 16);
            $table->timestamp('contacted_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palm_reading_bookings');
    }
};
