<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A course is sold on more than one term: pay monthly, or pay for a year up
     * front at a discount. That needs a row per interval rather than a single
     * row per course.
     *
     * The composite index is added before the old one is dropped: MySQL will
     * not release an index the foreign key still depends on, and it only lets
     * go once another index leads with the same column.
     */
    public function up(): void
    {
        Schema::table('course_plans', function (Blueprint $table) {
            $table->unique(['course_id', 'billing_interval'], 'course_plans_course_interval_unique');
        });

        Schema::table('course_plans', function (Blueprint $table) {
            $table->dropUnique('course_plans_course_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('course_plans', function (Blueprint $table) {
            $table->unique('course_id', 'course_plans_course_id_unique');
        });

        Schema::table('course_plans', function (Blueprint $table) {
            $table->dropUnique('course_plans_course_interval_unique');
        });
    }
};
