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
       Schema::create('settings', function (Blueprint $table) {
       $table->uuid('settings_id')->primary();

        $table->uuid('user_id')->unique();
        $table->foreign('user_id')
          ->references('user_id')
          ->on('users_profile')
          ->onDelete('cascade');

        $table->boolean('same_gender_provider')->default(false);
        $table->boolean('repeat_providers')->default(false);
        $table->boolean('auto_share')->default(false);
        $table->boolean('two_factor_auth')->default(false);
        $table->boolean('notifications')->default(true);

        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};