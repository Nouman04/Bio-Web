<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DemoTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $studentRole    = Role::firstOrCreate(['name' => 'student']);

        $superAdmin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'mnoumanb@gmail.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('nouman123'),
        ]);
        $superAdmin->assignRole($superAdminRole);

        // 3. Create Instructor User (1)
        $instructor = User::create([
            'name'              => 'Jane Instructor',
            'email'             => 'instructor@example.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
        ]);
        $instructor->assignRole($instructorRole);

        // 4. Create Students (8)
        for ($i = 1; $i <= 8; $i++) {$student = User::create([
                'name'              => "Student {$i}",
                'email'             => "student{$i}@example.com",
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ]);
            $student->assignRole($studentRole);
        }


    }
}
