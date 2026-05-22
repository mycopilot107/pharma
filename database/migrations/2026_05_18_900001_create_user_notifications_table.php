<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->string('action_url')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('remind_at');
            $table->string('priority')->default('normal');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'remind_at']);
            $table->unique(['user_id', 'type', 'reference_type', 'reference_id'], 'user_notification_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
