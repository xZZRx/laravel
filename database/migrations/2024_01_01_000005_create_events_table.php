<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: EVENT(event_id PK, name, type, category, scoring_method, status,
 * schedule, venue, organizer_id FK, tabulator_id FK).
 *
 * Two fields fix the biggest gap found against the thesis:
 *
 * - `scoring_method` (rank_based | points_based) is the Organizer-set
 *   switch Ch.4.4.1 describes: ARSO-style events convert event rank to
 *   points via a configured table (see rank_point_configs) before they
 *   reach Level 3; LMC-style events carry the Event Score forward
 *   directly. The previous build had no such switch — every event was
 *   implicitly medal-table LMC.
 * - `scoring_type` (criteria_based | by_round) is the thesis's own
 *   operational distinction between events with a Judge Interface
 *   (criteria-based) and events a Tabulator scores directly from match
 *   results (by-round), used throughout Module 4 and the use-case table.
 *
 * `event_category_id` carries the ERD's `category` field as a proper FK
 * to a weighted category (see event_categories), which is what makes
 * Level 3's category-weighted aggregation possible. `type` is kept as a
 * simple sports/academic/special tag for continuity with Module 2's
 * literal wording and for display/filtering.
 *
 * `tabulator_id`/`organizer_id` are the ERD's single "designated"
 * FKs (Module 2, key feature 5). Support for *additional* Tabulators/
 * TMs beyond this primary assignment — added after adviser feedback —
 * lives in the event_tabulators / event_tournament_managers pivot
 * tables and is additive, not a contradiction of the ERD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained('event_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type', 20); // 'sports' | 'academic' | 'special' — display tag, see note above
            $table->string('participation_type', 20)->default('institute'); // institute | team | individual
            $table->string('scoring_type', 20); // 'criteria_based' | 'by_round'
            $table->string('scoring_method', 20); // 'rank_based' | 'points_based'
            $table->string('status', 20)->default('draft'); // draft | ongoing | completed | cancelled | rescheduled
            $table->foreignId('organizer_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->foreignId('tabulator_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('venue')->nullable();
            $table->dateTime('schedule')->nullable();
            $table->dateTime('rescheduled_from')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->unsignedTinyInteger('reschedule_count')->default(0);
            $table->boolean('leaderboard_visible')->default(false);
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['season_id', 'slug']);
            $table->index(['season_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
