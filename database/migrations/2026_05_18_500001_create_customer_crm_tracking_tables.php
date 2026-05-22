<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('due_at');
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'due_at']);
            $table->index(['customer_id', 'due_at']);
        });

        Schema::create('customer_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('strength')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->nullable();
            $table->date('prescribed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'prescribed_at']);
        });

        Schema::create('customer_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('purchase_frequency')->nullable();
            $table->date('purchased_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'purchased_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_purchases');
        Schema::dropIfExists('customer_prescriptions');
        Schema::dropIfExists('customer_follow_ups');
    }
};
