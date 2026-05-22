<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('brand')->nullable();
            $table->string('strength')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('category')->nullable();
            $table->decimal('mrp', 12, 2)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'sku']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->string('status')->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('manager_notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'user_id', 'order_date']);
            $table->index(['company_id', 'customer_id']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
