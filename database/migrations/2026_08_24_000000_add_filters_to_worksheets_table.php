<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the reader actually asked for when they built the worksheet.
 *
 * The questions alone cannot say this: a topic that had nothing in the chosen
 * year contributes no questions, yet the cover page still has to list it as
 * something that was asked for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worksheets', function (Blueprint $table) {
            $table->json('filters')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('worksheets', function (Blueprint $table) {
            $table->dropColumn('filters');
        });
    }
};
