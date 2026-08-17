<?php

namespace Database\Seeders;

use App\Models\QuestionCategory;
use Illuminate\Database\Seeder;

class QuestionCategoriesSeeder extends Seeder
{
    /**
     * The two kinds of question the bank supports. They are referenced by id
     * from `question_bank.question_categories_id`, so both rows need to exist
     * before questions can be created against them.
     */
    public function run(): void
    {
        foreach (['theory', 'mcqs'] as $type) {
            QuestionCategory::firstOrCreate(['type' => $type]);
        }
    }
}
