<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('period_type'); // DAILY, WEEKLY, MONTHLY
            $table->decimal('total_gmv', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->integer('new_users_count')->default(0);
            $table->integer('active_merchants')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['report_date', 'period_type']);
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_reports');
    }
};