<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->foreignId('payment_account_id')->nullable()->constrained('manual_payment_accounts')->nullOnDelete();
            $table->string('payment_method', 64); // manual_pagomovil, manual_bank_transfer, manual_zelle, manual_binance
            $table->string('token', 64)->unique();
            $table->string('reference_number')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_id', 30)->nullable(); // Cédula o RIF del pagador
            $table->string('payer_phone', 30)->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payer_bank')->nullable(); // Banco de origen del cliente
            $table->decimal('paid_amount', 16, 4)->nullable();
            $table->string('paid_currency', 5)->nullable(); // VES, USD, USDT
            $table->decimal('exchange_rate', 16, 4)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('proof_image_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->string('proof_mime', 100)->nullable();
            $table->unsignedBigInteger('proof_size')->nullable();
            $table->string('status', 32)->default('pending_submission'); // pending_submission, pending_verification, approved, rejected, expired
            $table->text('admin_notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->unsignedInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            if (Schema::hasTable('orders')) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            }

            if (Schema::hasTable('admins')) {
                $table->foreign('verified_by')->references('id')->on('admins')->nullOnDelete();
            }

            $table->index('order_id');
            $table->index('payment_method');
            $table->index('reference_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payment_receipts');
    }
};
