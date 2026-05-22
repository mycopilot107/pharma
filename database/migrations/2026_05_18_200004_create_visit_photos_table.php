<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_photos');
    }
};
