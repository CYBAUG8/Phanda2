<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ReviewSeeder::class,
            UserSeeder::class,
            ProviderProfileSeeder::class,
            UserDashboardSummarySeeder::class,
            AddressSeeder::class,
            CategorySeeder::class,
            ServiceSeeder::class,
            //InitiateConversationSeeder::class 
        ]);
    }
}