<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: DEDUCTION(deduction_id PK, event_id FK, participant_id FK,
 * tabulator_id FK, level ENUM(judge, event, overall), value, reason,
 * created_at).
 *
 * This is the field-for-field fix for the previous build's single
 * deduction-per-score-entry design. `level` determines where the
 * penalty is subtracted in ScoreComputationService:
 *   - 'judge'   → subtracted from one judge's total for that
 *                 participant before Level 2 averages the judges
 *                 (requires `judge_id`)
 *   - 'event'   → subtracted from the participant's Event Score at
 *                 Level 2, after judges are averaged (or from
 *                 overall_score directly for by-round events)
 *   - 'overall' → subtracted once from the institute's Level 3
 *                 Overall Score for the season, independent of any
 *                 single event
 *
 * 'overall' level deductions have no event_id to derive a season from,
 * so `season_id` is stored directly (for judge/event level it just
 * mirrors event.season_id, kept for uniform querying).
 *
 * `institute_id` is who the deduction is actually against — always
 * set. `participant_id` links to one event's specific Participant row
 * and is required for 'judge'/'event' level (which are always tied to
 * one event) but left null for 'overall' level, for the same reason
 * leaderboard_snapshots keys standings by institute_id rather than
 * participant_id: there is no single participant row that represents
 * an institute across an entire season.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id('deduction_id');
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants', 'participant_id')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events', 'event_id')->cascadeOnDelete();
                        $table->foreignId('tabulator_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->foreignId('judge_id')->nullable()->constrained('judges')->cascadeOnDelete();
            $table->string('level', 10); // 'judge' | 'event' | 'overall'
            $table->decimal('value', 8, 2);
            $table->text('reason');
            $table->timestamps();

            $table->index(['institute_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
