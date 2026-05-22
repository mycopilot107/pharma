<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('expense_date');
            $table->string('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('manager_notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
