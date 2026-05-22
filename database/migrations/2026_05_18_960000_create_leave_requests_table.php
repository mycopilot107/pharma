<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 4, 1);
            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_period')->nullable();
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('manager_notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
