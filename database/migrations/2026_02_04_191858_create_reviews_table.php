<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('service_id');   
            $table->uuid('provider_id');   
            $table->uuid('user_id');        
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment');
            $table->timestamps();
        });

        Schema::table('service_reviews', function (Blueprint $table) {
            $table->index('service_id');            // Index on service_id
            $table->index('provider_id');           // Index on provider_id
            $table->unique(['user_id', 'provider_id']); // Unique constraint on user + provider
        });

    }

    public function down(): void {
        Schema::dropIfExists('service_reviews');
    }
};
