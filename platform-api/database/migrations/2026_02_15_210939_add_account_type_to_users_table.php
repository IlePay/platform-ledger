<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['PERSONAL', 'MERCHANT'])->default('PERSONAL')->after('role');
            $table->string('business_name')->nullable()->after('account_type');
            $table->string('business_type')->nullable()->after('business_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'business_name', 'business_type']);
        });
    }
};