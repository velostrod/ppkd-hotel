<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->string('checkin_time', 5)->default('14:00')->after('booking_prefix');
            $table->string('checkout_time', 5)->default('12:00')->after('checkin_time');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn(['checkin_time', 'checkout_time']);
        });
    }
};
