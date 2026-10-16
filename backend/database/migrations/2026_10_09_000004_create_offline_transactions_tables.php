<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('buyer_name');
            $table->unsignedBigInteger('total');
            $table->string('payment_method');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('offline_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offline_transaction_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('offline_transaction_items');
        Schema::dropIfExists('offline_transactions');
    }
};
