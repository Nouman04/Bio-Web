<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Model events stay switched on here.
     *
     * This used to `use WithoutModelEvents`, which muted the `creating` hook
     * the HasUuid trait boots — so every seeded row landed with a null uuid.
     * Routes are keyed on the uuid, so those rows could not be linked to at
     * all: route('students.show', $student->uuid) threw "Missing parameter".
     */
    public function run(): void
    {
        $this->call([
            CategoriesSeeder::class,
            QuestionCategoriesSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}
