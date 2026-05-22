<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('user_limit');
            $table->unsignedInteger('amount_paid_inr')->default(0);
            $table->string('status')->default('pending');
            $table->string('razorpay_order_id')->nullable()->index();
            $table->string('razorpay_payment_id')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
