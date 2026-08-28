<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A note of type `summary` points at the chapter summary it was written
     * from. Nullable, because exam notes have no summary behind them.
     */
    public function up(): void
    {
        if (Schema::hasColumn('notes', 'summary_id')) {
            return;
        }

        Schema::table('notes', function (Blueprint $table) {
            $table->foreignId('summary_id')->nullable()->after('topic_id')
                ->constrained('summaries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('summary_id');
        });
    }
};
