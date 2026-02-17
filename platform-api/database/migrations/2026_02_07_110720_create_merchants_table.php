<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('ledger_account_id')->nullable();
            
            // API Keys
            $table->string('api_key')->unique()->nullable();
            $table->string('api_secret')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            
            // KYB
            $table->enum('kyb_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->json('kyb_data')->nullable();
            
            // Settings
            $table->decimal('fee_percentage', 5, 2)->default(2.00);
            $table->boolean('auto_settlement')->default(false);
            $table->integer('settlement_day')->default(1); // D+1
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
