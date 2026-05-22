<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('route_date');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('status')->default('planned');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'route_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_routes');
    }
};
