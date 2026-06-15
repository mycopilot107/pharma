<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_plan_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');                   // 1=Mon … 6=Sat
            $table->tinyInteger('sort_order')->default(1);
            $table->string('area', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tour_plan_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_plan_stops');
    }
};
