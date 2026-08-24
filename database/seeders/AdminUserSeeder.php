<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        User::updateOrCreate(
            [
                'email' => 'admin@amar.test',
            ],
            [
                'name' => 'Administrador',
                'password' => Hash::make(
                    'Amar@123456'
                ),
            ]
        );
    }
}