<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Content resources instructors author and students read.
     */
    private array $contentResources = [
        'chapter',
        'topic',
        'quiz',
        'question',
        'guide',
        'diagram',
        'video lesson',
        'flashcard',
        'note',
        'summary',
    ];

    /**
     * Run the database seeds.
     *
     * Expects PermissionSeeder to have run first.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $instructor = Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'web']);
        $student = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);

        // Admin runs the whole panel.
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Instructors author content and read the course/student records they teach.
        $instructorPermissions = collect($this->contentResources)
            ->crossJoin(['add', 'edit', 'view', 'delete'])
            ->map(fn ($pair) => "{$pair[1]} {$pair[0]}")
            ->merge(['view course', 'edit course', 'view student'])
            ->all();

        $instructor->syncPermissions(
            Permission::whereIn('name', $instructorPermissions)->where('guard_name', 'web')->get()
        );

        // Students only read published material.
        $studentPermissions = collect($this->contentResources)
            ->map(fn ($resource) => "view {$resource}")
            ->merge(['view course'])
            ->all();

        $student->syncPermissions(
            Permission::whereIn('name', $studentPermissions)->where('guard_name', 'web')->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
