<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // CUSTOMER
        // =========================
        $customers= [
            [
                'email' => 'customer@example.com',
                'user_id'   => Str::uuid(),
                'full_name' => 'Customer User',
                'phone'     => '0815453389',
            ],
            [
                'email' => 'customer2@example.com',
                'user_id'   => Str::uuid(),
                'full_name' => 'Customer2 ',
                'phone'     => '0815551487',
            ],
            [
                'email' => 'customer3@example.com',
                'user_id'   => Str::uuid(),
                'full_name' => 'Customer3 User',
                'phone'     => '0823232121',
            ],
            [
                'email' => 'customer4@example.com',
                'user_id'   => Str::uuid(),
                'full_name' => 'Customer4 User',
                'phone'     => '0812123456',
            ],
            [
                'email' => 'customer5@example.com',
                'user_id'   => Str::uuid(),
                'full_name' => 'Customer5 ',
                'phone'     => '0813543212',
            ],
            [
                'email' => 'customer6@example.com',
                'user_id'   => Str::uuid(),
                'full_name' => 'Customer6 ',
                'phone'     => '0623459876',
            ],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'user_id'   => Str::uuid(),
                    'full_name' => $customer['full_name'],
                    'phone'     => $customer['phone'],
                    'password'  => Hash::make('password@123'),
                    'role'      => 'customer',
                ]
            );
        }

        // =========================
        // PROVIDERS
        // =========================
        $providers = [
            [
                'email' => 'generalCleaning@example.com',
                'full_name' => 'General Cleaning Service',
                'phone' => '0829921897',
            ],
            [
                'email' => 'plumbing@example.com',
                'full_name' => 'Aaron Plumbing Services',
                'phone' => '0620201598',
            ],
            [
                'email' => 'provider@example.com',
                'full_name' => 'Catering Services',
                'phone' => '0825513256',
            ],
            [
                'email' => 'ElectricalRepair@example.com',
                'full_name' => 'Electrical Repair Services',
                'phone' => '0823435523',
            ],
            [
                'email' => 'BantuConstruction@example.com',
                'full_name' => 'Bantu Construction Services',
                'phone' => '0734567890',
            ],
        ];

        foreach ($providers as $provider) {
            User::updateOrCreate(
                ['email' => $provider['email']],
                [
                    'user_id'   => Str::uuid(),
                    'full_name' => $provider['full_name'],
                    'phone'     => $provider['phone'],
                    'password'  => Hash::make('password@123'),
                    'role'      => 'provider',
                ]
            );
        }

        // =========================
        // ADMIN
        // =========================
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'user_id'   => Str::uuid(),
                'full_name' => 'Admin User',
                'phone'     => '0833333333',
                'password'  => Hash::make('password@123'),
                'role'      => 'admin',
            ]
        );
    }
}