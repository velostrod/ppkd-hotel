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
        // 1. housekeeping_requests
        Schema::create('housekeeping_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('request_type', ['stayover_cleaning', 'checkout_cleaning', 'deep_cleaning', 'maintenance', 'linen_replacement']);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->dateTime('request_time');
            $table->dateTime('completed_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. housekeeping_request_items
        Schema::create('housekeeping_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housekeeping_request_id')->constrained('housekeeping_requests')->onDelete('cascade');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });

        // 3. room_inspections
        Schema::create('room_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('cascade');
            $table->foreignId('inspected_by')->constrained('users')->onDelete('restrict');
            $table->dateTime('inspection_date');
            $table->enum('room_condition', ['good', 'needs_cleaning', 'damaged'])->default('good');
            $table->boolean('damage_found')->default(false);
            $table->decimal('damage_cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('completed');
            $table->timestamps();
        });

        // 4. room_inspection_items
        Schema::create('room_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_inspection_id')->constrained('room_inspections')->onDelete('cascade');
            $table->string('item_name');
            $table->enum('condition', ['good', 'damaged', 'missing'])->default('good');
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. laundry_requests
        Schema::create('laundry_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('restrict');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('request_date');
            $table->enum('status', ['requested', 'picked_up', 'processing', 'ready', 'delivered', 'cancelled'])->default('requested');
            $table->text('notes')->nullable();
            $table->decimal('total_charge', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. fnb_orders
        Schema::create('fnb_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('restrict');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('order_time');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. fnb_order_items
        Schema::create('fnb_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fnb_order_id')->constrained('fnb_orders')->onDelete('cascade');
            $table->foreignId('food_item_id')->constrained('food_items')->onDelete('restrict');
            $table->integer('qty');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fnb_order_items');
        Schema::dropIfExists('fnb_orders');
        Schema::dropIfExists('laundry_requests');
        Schema::dropIfExists('room_inspection_items');
        Schema::dropIfExists('room_inspections');
        Schema::dropIfExists('housekeeping_request_items');
        Schema::dropIfExists('housekeeping_requests');
    }
};
