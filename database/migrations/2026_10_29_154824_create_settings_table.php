<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'pricing.section.ngu_hanh' => [49000, 'Giá lẻ phần 1 - Ngũ hành tướng pháp (VNĐ)'],
            'pricing.section.than_khi_sac' => [49000, 'Giá lẻ phần 2 - Thần - Khí - Sắc (VNĐ)'],
            'pricing.section.tam_dinh' => [49000, 'Giá lẻ phần 3 - Tam đình (VNĐ)'],
            'pricing.section.ngu_quan' => [49000, 'Giá lẻ phần 4 - Ngũ quan (VNĐ)'],
            'pricing.section.thap_nhi_cung' => [69000, 'Giá lẻ phần 5 - Thập nhị cung (VNĐ)'],
            'pricing.section.phong_thuy' => [49000, 'Giá lẻ phần 6 - Phong thủy cải vận (VNĐ)'],
            'pricing.section.advice' => [39000, 'Giá lẻ phần 7 - Lời khuyên (VNĐ)'],
            'pricing.full_package' => [199000, 'Giá trọn gói, mở toàn bộ các phần (VNĐ)'],
            'pricing.upgrade_credit_percent' => [50, 'Khi lên trọn gói: được trừ bao nhiêu % số tiền đã mua lẻ'],
            'analysis.free_sections' => [['ngu_hanh'], 'Các phần miễn phí ngoài Tổng quan'],
        ];

        DB::table('settings')->insert(collect($defaults)->map(fn (array $setting, string $key): array => [
            'key' => $key,
            'value' => json_encode($setting[0]),
            'description' => $setting[1],
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
