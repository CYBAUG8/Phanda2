<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->unique();
            $table->foreign('user_id')
            ->references('user_id')
            ->on('users_profile');
            $table->string('otp', 6);
            $table->string('field'); // email or phone
            $table->string('value'); // email address or phone number
            $table->string('purpose')->default('verification'); // verification, password_reset, etc.
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps');
    }
};