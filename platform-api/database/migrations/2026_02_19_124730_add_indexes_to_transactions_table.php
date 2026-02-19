<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('from_user_id');
            $table->index('to_user_id');
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
            $table->index(['from_user_id', 'created_at']);
            $table->index(['to_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['from_user_id']);
            $table->dropIndex(['to_user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['from_user_id', 'created_at']);
            $table->dropIndex(['to_user_id', 'created_at']);
        });
    }
};