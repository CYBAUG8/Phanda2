<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->uuid('login_history_id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users_profile')
                ->onDelete('cascade');

            $table->timestamp('login_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('success');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('login_histories', function (Blueprint $table) {
            $table->dropColumn('login_at');
        });
    }
};