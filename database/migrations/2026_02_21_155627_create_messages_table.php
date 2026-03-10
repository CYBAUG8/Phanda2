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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('message_id')->primary();

            $table->uuid('conversation_id');
            $table->foreign('conversation_id')
              ->references('conversation_id')
              ->on('conversations')
              ->cascadeOnDelete();


            $table->uuid('sender_id');  
            $table->string('sender_type'); 

            $table->text('message');
            $table->boolean('is_read')->default(false);

            $table->timestamps();

             $table->index(['conversation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};