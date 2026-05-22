<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_pings', function (Blueprint $table) {
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 6, 2)->nullable();
            $table->decimal('altitude', 8, 2)->nullable();
            $table->unsignedTinyInteger('battery_percent')->nullable();
            $table->boolean('is_background')->default(false);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedSmallInteger('geofence_radius_meters')->default(150);
            $table->boolean('geofence_auto_checkin')->default(true);
        });

        Schema::create('geofence_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type'); // enter, exit
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('auto_triggered')->default(false);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
            $table->index(['customer_id', 'event_type']);
        });

        Schema::create('visit_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->json('flags')->nullable();
            $table->decimal('distance_from_customer_m', 10, 2)->nullable();
            $table->boolean('gps_verified')->default(false);
            $table->timestamp('validated_at');
            $table->timestamps();

            $table->unique('visit_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->boolean('geofence_checkin')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('geofence_checkin');
        });
        Schema::dropIfExists('visit_validations');
        Schema::dropIfExists('geofence_events');
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['geofence_radius_meters', 'geofence_auto_checkin']);
        });
        Schema::table('location_pings', function (Blueprint $table) {
            $table->dropColumn(['speed', 'heading', 'altitude', 'battery_percent', 'is_background']);
        });
    }
};
