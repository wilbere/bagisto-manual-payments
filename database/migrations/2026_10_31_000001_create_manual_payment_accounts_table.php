<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32); // pagomovil, bank_transfer, zelle, binance
            $table->string('title'); // ej. "Banesco Pago Móvil", "Zelle Principal"
            $table->string('bank_code', 10)->nullable(); // ej. '0134'
            $table->string('bank_name')->nullable(); // ej. 'Banesco'
            $table->string('account_number', 50)->nullable(); // 20 dígitos para transferencias
            $table->string('holder_name'); // Nombre del titular / beneficiario
            $table->string('holder_id', 30)->nullable(); // Cédula o RIF (ej. V-12345678, J-12345678-0)
            $table->string('phone', 30)->nullable(); // Teléfono para Pago Móvil
            $table->string('email')->nullable(); // Correo para Zelle
            $table->string('binance_id', 100)->nullable(); // Binance Pay ID / Paymail
            $table->string('qr_image_path')->nullable(); // QR de Pago Móvil o Binance
            $table->text('instructions')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payment_accounts');
    }
};
