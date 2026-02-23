<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->string('frequency'); // MONTHLY, QUARTERLY, YEARLY
            $table->date('next_payment_date');
            $table->boolean('auto_pay')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamps();
            
            $table->index(['payer_id', 'next_payment_date']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_payments');
    }
};