<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->enum('method', ['cash', 'card', 'sslcommerz', 'bkash', 'stripe', 'other'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('paid');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_invoice_id')
                ->nullable()
                ->after('shipment_id')
                ->constrained('payment_invoices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_invoice_id');
        });

        Schema::dropIfExists('payment_invoices');
    }
};
