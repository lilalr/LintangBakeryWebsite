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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('final_payment_proof')->nullable()->after('payment_proof');
            $table->string('final_payment_method')->nullable()->after('final_payment_proof');
            $table->string('settlement_proof')->nullable()->after('final_payment_method');
            $table->string('settlement_status')->nullable()->after('settlement_proof');
            $table->timestamp('remaining_paid_at')->nullable()->after('settlement_status');
            $table->string('courier_name')->nullable()->after('shipping_cost');
            $table->string('tracking_number')->nullable()->after('courier_name');
            $table->text('shipping_notes')->nullable()->after('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'final_payment_proof',
                'final_payment_method',
                'settlement_proof',
                'settlement_status',
                'remaining_paid_at',
                'courier_name',
                'tracking_number',
                'shipping_notes',
            ]);
        });
    }
};
