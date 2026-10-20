<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('min_stock')->default(10)->after('stock');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('stock_deducted')->default(false)->after('status');
        });

        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admin_name')->default('Admin');
            $table->enum('type', ['in', 'out']);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('balance');
            $table->string('reason');
            $table->string('source')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('loss_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('mutation_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_deducted');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_stock');
        });
    }
};
