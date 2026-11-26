<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();
            $table->string('product_name'); // snapshot
            $table->string('product_image')->nullable(); // snapshot
            $table->decimal('unit_price', 10, 2); // price at time of order
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('affiliate_commission_rate', 5, 2)->default(0);
            $table->decimal('affiliate_commission_amount', 10, 2)->default(0);
            $table->boolean('commission_generated')->default(false);
            $table->timestamps();
        });

        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('seller_notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_orders');
        Schema::dropIfExists('order_items');
    }
};
