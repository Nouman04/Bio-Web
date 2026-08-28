<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Actions applied to every resource, following the "add course",
     * "edit course", "view course", "delete course" naming scheme.
     */
    private array $actions = ['add', 'edit', 'view', 'delete'];

    /**
     * Resources the admin panel manages, named in the singular.
     */
    private array $resources = [
        'course',
        'chapter',
        'topic',
        'category',
        'quiz',
        'question',
        'guide',
        'diagram',
        'video lesson',
        'flashcard',
        'note',
        'summary',
        'student',
        'user',
        'role',
        'permission',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->resources as $resource) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action} {$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
