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
        Schema::table('face_analyses', function (Blueprint $table) {
            $table->string('face_crop_path')->nullable()->after('portrait_path');
            $table->json('face_metrics')->nullable()->after('face_crop_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('face_analyses', function (Blueprint $table) {
            $table->dropColumn(['face_crop_path', 'face_metrics']);
        });
    }
};
