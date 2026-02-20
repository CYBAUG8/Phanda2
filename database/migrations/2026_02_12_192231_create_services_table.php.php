<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table)
         {
            $table->uuid('service_id')->primary();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            
           $table->uuid('provider_id');
           $table->foreign('provider_id')
                 ->references('provider_id')
                 ->on('provider_profiles')
                 ->cascadeOnDelete();
      

            $table->string('provider_name');
            $table->string('title');
            $table->text('description');
            $table->decimal('base_price', 10, 2);
            $table->integer('min_duration')->default(60);
            $table->string('location');
            $table->decimal('rating', 2, 1)->default(0.0);
            $table->integer('reviews_count')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};