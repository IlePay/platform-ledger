<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('pin')->nullable()->after('password');
            $table->string('ledger_account_id')->nullable()->after('pin');
            
            // KYC
            $table->enum('kyc_level', ['NONE', 'BASIC', 'STANDARD', 'PREMIUM'])->default('NONE')->after('ledger_account_id');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_level');
            $table->json('kyc_data')->nullable()->after('kyc_verified_at');
            
            // Limits
            $table->decimal('daily_limit', 19, 2)->default(50000)->after('kyc_data');
            $table->decimal('monthly_limit', 19, 2)->default(500000)->after('daily_limit');
            
            // Status
            $table->boolean('is_active')->default(true)->after('monthly_limit');
            $table->timestamp('phone_verified_at')->nullable()->after('is_active');
            
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'first_name', 'last_name', 'pin', 'ledger_account_id',
                'kyc_level', 'kyc_verified_at', 'kyc_data',
                'daily_limit', 'monthly_limit', 'is_active', 'phone_verified_at'
            ]);
            $table->dropSoftDeletes();
        });
    }
};