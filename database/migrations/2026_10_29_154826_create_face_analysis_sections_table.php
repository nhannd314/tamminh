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
        Schema::create('face_analysis_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('face_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->json('content')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['face_analysis_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_analysis_sections');
    }
};
