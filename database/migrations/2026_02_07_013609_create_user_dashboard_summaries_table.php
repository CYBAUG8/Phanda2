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
       Schema::create('user_dashboard_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name')->unique();

            // bookings
            $table->unsignedInteger('bookings_requested')->default(0);
            $table->unsignedInteger('bookings_offered')->default(0);
            $table->unsignedInteger('bookings_accepted')->default(0);
            $table->unsignedInteger('bookings_in_progress')->default(0);

            // messages
            $table->unsignedInteger('unread_messages')->default(0);

            // average rating
            $table->decimal('average_rating', 10, 2)->default(0);

            // activity filtering
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_summaries');
    }
};
