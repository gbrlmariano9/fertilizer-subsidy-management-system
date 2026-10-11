<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payout_method');
            $table->string('payout_account');
            $table->string('status')->default('pending');
            $table->string('reference')->nullable()->unique();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
