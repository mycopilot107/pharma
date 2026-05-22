<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('price_usd', 8, 2)->default(0);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('price_inr');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('amount_paid_usd', 8, 2)->default(0);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('amount_paid_inr');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_usd', 8, 2)->default(0);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('amount_inr');
            $table->string('currency', 3)->default('USD')->change();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('price_inr')->default(0);
        });
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('price_usd');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('amount_paid_inr')->default(0);
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('amount_paid_usd');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('amount_inr')->default(0);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('amount_usd');
            $table->string('currency', 3)->default('INR')->change();
        });
    }
};
