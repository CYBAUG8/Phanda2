<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('users_profile', function (Blueprint $table) {
        $table->uuid('user_id')->primary();

        
        $table->string('full_name', 160)->nullable();
        $table->string('email', 254)->unique()->nullable();
        $table->string('phone', 32)->unique()->nullable();
        $table->text('password')->nullable();

       
        $table->enum('gender', ['male', 'female', 'other'])->nullable();
        $table->string('member_id')->nullable();

        
        $table->enum('role', ['CUSTOMER', 'PROVIDER', 'ADMIN'])
              ->default('CUSTOMER');

        $table->enum('account_status', ['ACTIVE', 'SUSPENDED', 'DELETED'])
              ->default('ACTIVE');

        
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamp('phone_verified_at')->nullable();

        
        $table->timestamp('last_login_at')->nullable();
        $table->rememberToken()->nullable();

      
        $table->timestamps();
        $table->softDeletes();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('users_profile');
    }
};