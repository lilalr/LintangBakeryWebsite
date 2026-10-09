<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price_per_bal');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('harga_modal_at_purchase', 12, 2)->default(0)->after('subtotal');
            $table->decimal('harga_jual_at_purchase', 12, 2)->default(0)->after('harga_modal_at_purchase');
        });

        Schema::table('offline_transaction_items', function (Blueprint $table) {
            $table->decimal('harga_modal_at_purchase', 12, 2)->default(0)->after('subtotal');
            $table->decimal('harga_jual_at_purchase', 12, 2)->default(0)->after('harga_modal_at_purchase');
        });

        Schema::table('offline_transactions', function (Blueprint $table) {
            $table->decimal('total_hpp', 12, 2)->default(0)->after('total');
            $table->decimal('total_laba', 12, 2)->default(0)->after('total_hpp');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('offline_transactions', function (Blueprint $table) {
            $table->dropColumn(['total_hpp', 'total_laba']);
        });

        Schema::table('offline_transaction_items', function (Blueprint $table) {
            $table->dropColumn(['harga_modal_at_purchase', 'harga_jual_at_purchase']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['harga_modal_at_purchase', 'harga_jual_at_purchase']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
