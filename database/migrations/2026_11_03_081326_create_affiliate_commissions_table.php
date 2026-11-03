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
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();

            // Đơn vật phẩm (orders) hoặc đơn mở khoá xem tướng (analysis_orders); mỗi đơn chỉ một khoản hoa hồng.
            $table->string('commissionable_type', 32);
            $table->unsignedBigInteger('commissionable_id');
            $table->unique(['commissionable_type', 'commissionable_id'], 'affiliate_commissions_commissionable_unique');

            $table->unsignedInteger('base_amount');
            $table->unsignedTinyInteger('rate_percent');
            $table->unsignedInteger('amount');

            $table->string('status', 16)->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payout_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
