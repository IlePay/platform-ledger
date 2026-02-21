<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status')->default('PENDING')->after('kyc_level'); // PENDING, APPROVED, REJECTED
            $table->string('id_document_path')->nullable()->after('kyc_status');
            $table->string('proof_address_path')->nullable()->after('id_document_path');
            $table->text('kyc_rejection_reason')->nullable()->after('proof_address_path');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_rejection_reason');
            $table->foreignId('kyc_verified_by')->nullable()->constrained('admin_users')->after('kyc_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'id_document_path',
                'proof_address_path',
                'kyc_rejection_reason',
                'kyc_verified_at',
                'kyc_verified_by'
            ]);
        });
    }
};