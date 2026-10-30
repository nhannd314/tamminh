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
        Schema::create('face_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('gender', 10);
            $table->date('birth_date');
            $table->unsignedTinyInteger('birth_hour')->nullable();
            $table->unsignedTinyInteger('birth_minute')->nullable();
            $table->string('portrait_path');

            $table->string('status', 20)->default('pending')->index();
            $table->string('ai_model', 60)->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('analyzed_at')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('terms_accepted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_analyses');
    }
};
