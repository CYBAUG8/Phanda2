<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('location_id')->primary();

            $table->uuid('user_id')->unique();
            $table->foreign('user_id')
            ->references('user_id')
            ->on('users_profile')
            ->onDelete('cascade');

           
            $table->string('name');
            $table->text('address');
            $table->enum('type', ['home', 'work', 'other'])->default('home');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations');
    }
};