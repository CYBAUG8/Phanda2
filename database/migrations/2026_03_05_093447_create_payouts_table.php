<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {

            $table->uuid('payout_id')->primary();

            $table->uuid('provider_id');

            $table->decimal('amount', 10, 2);

            $table->char('currency', 3)->default('ZAR');

            $table->enum('status', [
                'SCHEDULED',
                'PAID',
                'FAILED'
            ])->default('SCHEDULED');

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->string('reference', 128)->nullable();

            $table->timestamps();

            $table->foreign('provider_id')
                  ->references('provider_id')
                  ->on('provider_profiles')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};