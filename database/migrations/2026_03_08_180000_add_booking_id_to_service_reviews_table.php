<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('service_reviews', 'booking_id')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->uuid('booking_id')->nullable()->after('service_id');
            });
        }

        // Add a dedicated index so MySQL FKs no longer depend on the legacy composite unique index.
        if (!$this->hasIndex('service_reviews', 'service_reviews_from_user_id_index')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->index('from_user_id');
            });
        }

        if (!$this->hasForeignKeyForColumn('service_reviews', 'booking_id')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->foreign('booking_id')
                    ->references('id')
                    ->on('bookings')
                    ->nullOnDelete();
            });
        }

        if ($this->hasIndex('service_reviews', 'service_reviews_from_user_id_to_user_id_unique')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE `service_reviews` DROP INDEX `service_reviews_from_user_id_to_user_id_unique`');
            } else {
                Schema::table('service_reviews', function (Blueprint $table) {
                    $table->dropUnique(['from_user_id', 'to_user_id']);
                });
            }
        }

        if (!$this->hasIndex('service_reviews', 'service_reviews_booking_id_unique')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->unique('booking_id');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('service_reviews', 'service_reviews_booking_id_unique')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE `service_reviews` DROP INDEX `service_reviews_booking_id_unique`');
            } else {
                Schema::table('service_reviews', function (Blueprint $table) {
                    $table->dropUnique(['booking_id']);
                });
            }
        }

        if ($this->hasForeignKeyForColumn('service_reviews', 'booking_id')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->dropForeign(['booking_id']);
            });
        }

        if (Schema::hasColumn('service_reviews', 'booking_id')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->dropColumn('booking_id');
            });
        }

        if (!$this->hasIndex('service_reviews', 'service_reviews_from_user_id_to_user_id_unique')) {
            Schema::table('service_reviews', function (Blueprint $table) {
                $table->unique(['from_user_id', 'to_user_id']);
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $safeTable = str_replace("'", "''", $table);
            $rows = DB::select("PRAGMA index_list('{$safeTable}')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
                [$table, $indexName]
            );

            return $row !== null;
        }

        return false;
    }

    private function hasForeignKeyForColumn(string $table, string $column): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $safeTable = str_replace("'", "''", $table);
            $rows = DB::select("PRAGMA foreign_key_list('{$safeTable}')");

            foreach ($rows as $row) {
                if (($row->from ?? null) === $column) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'SELECT 1 FROM information_schema.key_column_usage WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL LIMIT 1',
                [$table, $column]
            );

            return $row !== null;
        }

        return false;
    }
};
