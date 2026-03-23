<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('payment_method_id')->nullable()->after('user_id');
            $table->foreign('payment_method_id')
                ->references('payment_method_id')
                ->on('payment_methods')
                ->nullOnDelete();

            $table->index(['payment_method_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payment_method_id', 'status']);
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
    }
};
