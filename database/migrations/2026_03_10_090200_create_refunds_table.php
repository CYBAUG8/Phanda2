<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('refund_id')->primary();
            $table->uuid('payment_id');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'refunded', 'failed'])->default('refunded');
            $table->string('reason', 255)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('payment_id')->on('payments')->cascadeOnDelete();
            $table->index(['payment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
