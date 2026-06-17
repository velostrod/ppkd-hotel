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
        // 1. guests
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('id_number')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. room_types
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->integer('capacity');
            $table->boolean('breakfast_included')->default(false);
            $table->decimal('breakfast_price', 12, 2)->default(0);
            $table->boolean('extra_bed_allowed')->default(false);
            $table->decimal('extra_bed_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('restrict');
            $table->integer('floor');
            $table->enum('status', ['available', 'reserved', 'occupied', 'dirty', 'cleaning', 'inspected', 'maintenance', 'out_of_order'])->default('available');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. food_categories
        Schema::create('food_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 5. food_items
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_category_id')->constrained('food_categories')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 6. payment_methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. charge_types
        Schema::create('charge_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. hotel_settings
        Schema::create('hotel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(10.00); // e.g. 10.00%
            $table->decimal('service_charge_rate', 5, 2)->default(5.00); // e.g. 5.00%
            $table->decimal('breakfast_threshold', 12, 2)->default(600000.00); // standard threshold
            $table->string('invoice_prefix')->default('INV');
            $table->string('booking_prefix')->default('BK');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_settings');
        Schema::dropIfExists('charge_types');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('food_items');
        Schema::dropIfExists('food_categories');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('guests');
    }
};
