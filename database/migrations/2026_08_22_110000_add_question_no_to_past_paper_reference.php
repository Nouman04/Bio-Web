<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The question's number within the paper — "2(c)(i)". Worksheets carry it
     * and it is part of how a question is cited, but it had nowhere to go.
     */
    public function up(): void
    {
        Schema::table('past_paper_reference', function (Blueprint $table) {
            $table->string('question_no')->nullable()->after('paper_no');
        });
    }

    public function down(): void
    {
        Schema::table('past_paper_reference', function (Blueprint $table) {
            $table->dropColumn('question_no');
        });
    }
};
