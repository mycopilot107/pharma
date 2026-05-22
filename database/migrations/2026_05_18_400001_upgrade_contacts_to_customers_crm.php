<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('contacts', 'customers');

        Schema::table('customers', function (Blueprint $table) {
            $table->string('contact_person')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->renameColumn('contact_id', 'customer_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'contact_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreign('contact_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['contact_person', 'state', 'pincode', 'notes']);
        });

        Schema::rename('customers', 'contacts');
    }
};
