<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'discount')) {
                $table->decimal('discount', 5, 2)->default(0)->after('unit_price');
            }
            if (! Schema::hasColumn('order_items', 'remark')) {
                $table->string('remark')->nullable()->after('line_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('order_items', 'remark')   ? 'remark'   : null,
                Schema::hasColumn('order_items', 'discount') ? 'discount' : null,
            ]));
        });
    }
};
