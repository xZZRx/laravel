<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: SCORING_CRITERION(criterion_id PK, event_id FK, name, weight).
 *
 * Thesis Ch.4.4.1, Level 1: "each criterion's maximum score is set equal
 * to its percentage share of the 100-point basis... allowing the Judge
 * Total to be obtained as a direct sum of the raw scores entered,
 * without requiring multiplication." That means `max_score` here *is*
 * the ERD's `weight` column — a 20-point criterion is a 20%-weighted
 * criterion. This intentionally replaces the previous build's separate
 * max_score/weight_pct pair (which computed a traditional weighted
 * average instead of the criterion-allocated sum the thesis specifies).
 *
 * `parent_id` keeps optional sub-criteria grouping from the previous
 * build (e.g. "Execution" split into "Timing" / "Precision") — additive
 * UI sugar; only leaf criteria hold a max_score judges actually score
 * against, and a top-level criterion's max_score is the sum of its
 * children when it has any.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_criteria', function (Blueprint $table) {
            $table->id('criterion_id');
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('scoring_criteria', 'criterion_id')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_score', 6, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_criteria');
    }
};
