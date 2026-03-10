<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('conversation_id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')
              ->references('user_id')
              ->on('users')
              ->cascadeOnDelete();

            
            $table->uuid('provider_id');
            $table->foreign('provider_id')
              ->references('provider_id')
              ->on('provider_profiles')
              ->cascadeOnDelete();
              
            $table->timestamp('last_message_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
