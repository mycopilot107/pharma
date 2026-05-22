<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('product_name')->nullable();
            $table->string('area_name')->nullable();
            $table->decimal('target_value', 12, 2);
            $table->decimal('achieved_value', 12, 2)->default(0);
            $table->string('unit')->default('currency');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'type']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
