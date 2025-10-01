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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // customer
            $table->foreignId('courier_id')->nullable()->constrained('couriers')->nullOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // pickup
            $table->string('pickup_name')->nullable();
            $table->string('pickup_phone')->nullable();
            $table->text('pickup_address')->nullable();
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();

            // dropoff
            $table->string('drop_name');
            $table->string('drop_phone');
            $table->text('drop_address');
            $table->decimal('drop_lat', 10, 7)->nullable();
            $table->decimal('drop_lng', 10, 7)->nullable();

            $table->decimal('weight_kg', 8, 2)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('cost_of_delivery_amount', 10, 2)->default(0);
            $table->decimal('additional_charge', 10, 2)->default(0);
            $table->decimal('balance_cost', 10, 2)->default(0);
            $table->enum('status', ['pending','assigned','picked','in_transit','delivered','cancelled'])->default('pending');
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
