<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visit_type');
            $table->string('place_name');
            $table->text('address')->nullable();
            $table->string('status')->default('planned');
            $table->timestamp('planned_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
