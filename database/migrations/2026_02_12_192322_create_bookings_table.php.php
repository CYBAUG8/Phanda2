<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * @deprecated This legacy migration is retained only for migration history compatibility.
     * The canonical bookings table schema is created by 2026_03_02_003911_create_bookings_table.
     */
    public function up(): void
    {
        // Intentionally no-op to avoid creating a duplicate/legacy bookings table shape.
    }

    public function down(): void
    {
        // Intentionally no-op.
    }
};
