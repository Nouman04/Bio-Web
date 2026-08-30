<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the state of a record lives, for anything that has one — a
     * chapter's Draft/Published, a quiz's draft/published/closed, an attempt's
     * in_progress/graded and the rest. Kept apart from the records themselves
     * so state is one shape in one place; see the HasStatus trait.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('status');
            $table->morphs('statusable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};