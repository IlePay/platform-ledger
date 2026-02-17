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
            $table->string('ledger_transaction_id')->unique();
            $table->string('idempotency_key')->unique();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('from_account_id');
            $table->string('to_account_id');
            $table->decimal('amount', 19, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('type', ['TRANSFER', 'CREDIT', 'DEBIT', 'FEE', 'REFUND']);
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'REVERSED']);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['from_user_id', 'created_at']);
            $table->index(['to_user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
