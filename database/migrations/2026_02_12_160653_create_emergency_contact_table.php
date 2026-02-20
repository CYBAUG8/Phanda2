<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->uuid('emergency_contact_id')->primary();

            // user_id matches users.user_id
            $table->uuid('user_id');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users_profile')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('phone', 32);
            $table->string('relationship')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
