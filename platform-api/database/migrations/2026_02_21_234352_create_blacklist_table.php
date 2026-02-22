<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // IP, PHONE, EMAIL, USER_ID
            $table->string('value');
            $table->string('reason');
            $table->unsignedBigInteger('added_by');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'value']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist');
    }
};