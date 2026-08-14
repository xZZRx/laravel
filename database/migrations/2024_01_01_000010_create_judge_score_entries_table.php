<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: JUDGE_SCORE_ENTRY(entry_id PK, event_id FK, judge_id FK,
 * participant_id FK, criterion_id FK, raw_score, weighted_score,
 * submitted_at).
 *
 * Under the criterion-allocated model (see scoring_criteria), a
 * criterion's max_score already equals its weight, so no separate
 * multiplication step exists — `weighted_score` is kept only for
 * field-for-field parity with the ERD and is always written equal to
 * `raw_score` at save time (see ScoreComputationService). `signature`
 * backs the optimistic-concurrency check from Ch.4's Architecture
 * diagram: it's a hash of the judge's known score state at load time,
 * compared before an update is accepted so two simultaneous saves for
 * the same judge/event can't silently overwrite one another.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judge_score_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('judge_id')->constrained('judges')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants', 'participant_id')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('scoring_criteria', 'criterion_id')->cascadeOnDelete();
            $table->decimal('raw_score', 6, 2);
            $table->decimal('weighted_score', 6, 2);
            $table->string('signature', 64)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['judge_id', 'participant_id', 'criterion_id'], 'judge_score_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judge_score_entries');
    }
};
