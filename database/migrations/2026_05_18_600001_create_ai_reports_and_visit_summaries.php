<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->text('ai_summary')->nullable();
        });

        Schema::create('ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->date('report_date');
            $table->json('context_snapshot')->nullable();
            $table->json('detected_insights')->nullable();
            $table->longText('content');
            $table->timestamps();

            $table->index(['company_id', 'type', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reports');

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('ai_summary');
        });
    }
};
