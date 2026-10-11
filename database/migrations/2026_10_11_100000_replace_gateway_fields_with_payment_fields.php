<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('gateway_reference', 'payment_reference');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->renameColumn('gateway_event_id', 'event_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('payment_reference', 'gateway_reference');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->renameColumn('event_id', 'gateway_event_id');
        });
    }
};
