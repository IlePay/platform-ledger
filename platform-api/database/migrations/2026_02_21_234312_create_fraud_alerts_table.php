<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->string('alert_type'); // VELOCITY, HIGH_AMOUNT, SUSPICIOUS_PATTERN, BLACKLIST
            $table->string('severity'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, REVIEWED, APPROVED, BLOCKED
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('alert_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_alerts');
    }
};