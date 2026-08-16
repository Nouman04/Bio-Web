<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A flashcard deck is now a titled set of questions that hangs off any
     * piece of content — a note, video lesson, guide, summary, diagram, topic
     * and so on — rather than a single note/question/answer triple.
     *
     * The questions themselves live in the `assessments` table.
     */
    public function up(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->dropForeign(['note_id']);
            $table->dropForeign(['question_id']);
            $table->dropForeign(['question_answer_id']);
            $table->dropColumn(['note_id', 'question_id', 'question_answer_id']);
        });

        Schema::table('flashcards', function (Blueprint $table) {
            $table->string('title')->after('uuid');
            $table->foreignId('chapter_id')->nullable()->after('title')
                ->constrained('chapters')->nullOnDelete();
            $table->nullableMorphs('flashcardable');
        });
    }

    public function down(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->dropMorphs('flashcardable');
            $table->dropConstrainedForeignId('chapter_id');
            $table->dropColumn('title');
        });

        Schema::table('flashcards', function (Blueprint $table) {
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_bank')->cascadeOnDelete();
            $table->foreignId('question_answer_id')->constrained('question_answers')->cascadeOnDelete();
        });
    }
};
