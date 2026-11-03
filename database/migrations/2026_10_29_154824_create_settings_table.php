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
            'pricing.section.ngu_hanh' => [0, 'Giá lẻ phần 1 - Ngũ hành tướng pháp (VNĐ, 0 = miễn phí)'],
            'pricing.section.than_khi_sac' => [20000, 'Giá lẻ phần 2 - Thần - Khí - Sắc (VNĐ, 0 = miễn phí)'],
            'pricing.section.tam_dinh' => [20000, 'Giá lẻ phần 3 - Tam đình (VNĐ, 0 = miễn phí)'],
            'pricing.section.ngu_quan' => [20000, 'Giá lẻ phần 4 - Ngũ quan (VNĐ, 0 = miễn phí)'],
            'pricing.section.thap_nhi_cung' => [20000, 'Giá lẻ phần 5 - Thập nhị cung (VNĐ, 0 = miễn phí)'],
            'pricing.section.phong_thuy' => [20000, 'Giá lẻ phần 6 - Phong thủy cải vận (VNĐ, 0 = miễn phí)'],
            'pricing.section.advice' => [20000, 'Giá lẻ phần 7 - Lời khuyên (VNĐ, 0 = miễn phí)'],
            'pricing.full_package_discount_percent' => [20, 'Chiết khấu trọn gói (%) trên tổng giá lẻ các phần có phí'],        ];

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
