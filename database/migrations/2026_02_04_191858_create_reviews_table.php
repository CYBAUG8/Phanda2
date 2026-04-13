<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary();
            $table->string('service_id');
            $table->uuid('to_user_id');   // provider
            $table->uuid('from_user_id'); // customer
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('to_user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('from_user_id')->references('user_id')->on('users')->cascadeOnDelete();

            // Indexes
            $table->index('service_id');
            $table->index('to_user_id');

            // One review per customer per provider
            $table->unique(['from_user_id', 'to_user_id']);
        });

    }

    public function down(): void {
        Schema::dropIfExists('service_reviews');
    }
};
