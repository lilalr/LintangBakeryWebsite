<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('buyer');
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('dp')->default(0);
            $table->unsignedBigInteger('remaining')->default(0);
            $table->string('payment_type');
            $table->string('status')->default('Menunggu Konfirmasi');
            $table->string('payment_proof')->nullable();
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
