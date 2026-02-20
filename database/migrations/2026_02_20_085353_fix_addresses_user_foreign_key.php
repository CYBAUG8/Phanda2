<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            
            // Drop old foreign key
            $table->dropForeign(['user_id']);

            // Add new foreign key referencing users table
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {

            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_profile')
                  ->onDelete('cascade');
        });
    }
};