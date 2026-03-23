<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('payment_method_id')->primary();
            $table->uuid('user_id');
            $table->string('brand', 32);
            $table->string('holder_name', 120);
            $table->char('last_four', 4);
            $table->unsignedTinyInteger('expiry_month');
            $table->unsignedSmallInteger('expiry_year');
            $table->string('token', 191)->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
