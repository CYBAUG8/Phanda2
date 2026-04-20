<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->enum('payment_status', [
                'unpaid',
                'payment_required',
                'paid',
                'refunded',
                'failed',
            ])->default('unpaid')->after('status');

            $table->timestamp('payment_due_at')->nullable()->after('payment_status');
            $table->string('cancellation_reason')->nullable()->after('payment_due_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_due_at',
                'cancellation_reason',
                'cancelled_at',
            ]);
        });
    }
};