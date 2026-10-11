<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('farmer')->index();
            $table->string('beneficiary_id')->nullable()->unique();
            $table->decimal('subsidy_balance', 12, 2)->default(0);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['beneficiary_id']);
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'beneficiary_id',
                'subsidy_balance',
                'wallet_balance',
                'is_verified',
                'is_active',
            ]);
        });
    }
};
