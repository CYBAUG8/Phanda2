<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recovery_contacts', function (Blueprint $table) {
            $table->uuid('recovery_contact_id');

          
             $table->uuid('user_id')->unique();
             $table->foreign('user_id')
             ->references('user_id')
             ->on('users_profile')
             ->onDelete('cascade');

            $table->string('name', 160);
            $table->string('phone', 32);
            $table->string('email', 160)->nullable();
            $table->string('relationship', 50)->nullable();
            $table->timestamps();

           
        });
    }

    public function down()
    {
        Schema::dropIfExists('recovery_contacts');
    }
};