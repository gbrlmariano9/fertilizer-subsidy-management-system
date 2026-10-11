<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('merchant_id')->constrained('users')->restrictOnDelete();

            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('pending')->index();

            $table->string('qr_signature', 128)->nullable();
            $table->timestamp('qr_expires_at')->nullable();

            $table->string('gateway_reference')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['farmer_id', 'status']);
            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
