<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        $defaultPassword = Hash::make('test@123');

        // 2. Create 2 Admin Users
        $admins = [
            [
                'name' => 'Admin One',
                'email' => 'admin@gmail.com',
            ],
            [
                'name' => 'Nouman B',
                'email' => 'mnoumanb@gmail.com',
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => $defaultPassword,
                ]
            );
            $admin->assignRole($adminRole);
        }

        // 3. Create 3 Random Student Users
        for ($i = 1; $i <= 3; $i++) {
            $student = User::create([
                'name' => 'Student ' . $i,
                'email' => 'student' . $i . '@example.com', // Random/dummy emails
                'password' => $defaultPassword,
            ]);
            $student->assignRole($studentRole);
        }
    }
}
