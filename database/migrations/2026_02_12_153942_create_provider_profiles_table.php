<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->uuid('provider_id')->primary();
            
            $table->uuid('user_id');

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->string('business_name', 200);
            $table->text('bio')->nullable();
            $table->integer('years_experience')->default(0);
            $table->string('service_area')->nullable();
            $table->enum('kyc_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->boolean('is_online')->default(false);
            $table->decimal('service_radius_km', 5, 2)->nullable();
            $table->decimal('last_lat', 10, 7)->nullable();
            $table->decimal('last_lng', 10, 7)->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};