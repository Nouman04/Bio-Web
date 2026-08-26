<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An MCQ answer is right or wrong, so `is_correct` was enough. A written
     * answer is worth a number of marks an instructor decides, and usually
     * comes with a note explaining why.
     */
    public function up(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->decimal('marks_awarded', 8, 2)->nullable()->after('is_correct');
            $table->text('feedback')->nullable()->after('marks_awarded');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropColumn(['marks_awarded', 'feedback']);
        });
    }
};
