<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('last_latitude', 10, 7)->nullable();
            $table->decimal('last_longitude', 10, 7)->nullable();
            $table->timestamp('last_location_at')->nullable();
            $table->boolean('tracking_active')->default(false);
        });

        Schema::create('mr_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->timestamp('clock_in_at');
            $table->timestamp('clock_out_at')->nullable();
            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
        });

        Schema::create('location_pings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_route_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->string('source')->default('ping');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'recorded_at']);
            $table->index(['user_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_pings');
        Schema::dropIfExists('mr_attendances');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_latitude', 'last_longitude', 'last_location_at', 'tracking_active']);
        });
    }
};
