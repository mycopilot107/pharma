<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('contact_id')
                    ->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('visits', 'products_promoted')) {
                $table->json('products_promoted')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('visits', 'samples_given')) {
                $table->unsignedInteger('samples_given')->nullable()->after('products_promoted');
            }
            if (! Schema::hasColumn('visits', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable()->after('samples_given');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('visits', 'follow_up_date')   ? 'follow_up_date'   : null,
                Schema::hasColumn('visits', 'samples_given')    ? 'samples_given'    : null,
                Schema::hasColumn('visits', 'products_promoted')? 'products_promoted': null,
                Schema::hasColumn('visits', 'customer_id')      ? 'customer_id'      : null,
            ]));
        });
    }
};
