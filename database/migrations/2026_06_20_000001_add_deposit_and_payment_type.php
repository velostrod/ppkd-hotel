<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('deposit_amount', 12, 2)->default(0)->after('balance_due');
            $table->decimal('deposit_returned', 12, 2)->default(0)->after('deposit_amount');
            $table->enum('deposit_status', ['none', 'held', 'returned', 'partially_returned'])->default('none')->after('deposit_returned');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('type', 30)->default('room')->after('status');
        });

        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->decimal('security_deposit_amount', 12, 2)->default(0)->after('breakfast_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'deposit_returned', 'deposit_status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn('security_deposit_amount');
        });
    }
};
