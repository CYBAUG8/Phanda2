<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('address_id')->primary();

            $table->uuid('user_id')->unique();
            $table->foreign('user_id')
             ->references('user_id')
             ->on('users_profile')
             ->onDelete('cascade');
            $table->enum('type', ['home', 'work', 'billing', 'shipping', 'other'])->default('home');
            $table->string('street');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code', 20);
            $table->string('country')->default('south_africa');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};