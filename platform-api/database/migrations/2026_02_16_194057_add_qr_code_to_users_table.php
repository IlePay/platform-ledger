<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_code')->unique()->nullable()->after('business_type');
            $table->decimal('total_sales', 19, 2)->default(0)->after('qr_code');
            $table->integer('sales_count')->default(0)->after('total_sales');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'total_sales', 'sales_count']);
        });
    }
};