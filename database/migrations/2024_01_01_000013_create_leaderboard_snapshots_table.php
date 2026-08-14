<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: LEADERBOARD_SNAPSHOT(snapshot_id PK, event_id FK, participant_id
 * FK, computed_score, rank, is_visible, updated_at) — with the ERD's
 * own note: "stores computed standings only after Tabulator
 * confirmation. Rankings are generated dynamically from validated
 * source scores to maintain data consistency."
 *
 * That note is implemented literally: rows here are never hand-edited,
 * only upserted by ScoreComputationService whenever a Tabulator
 * finalizes an event or confirms overall standings. `event_id` nullable
 * + `season_id` lets one table hold both scopes: an event_id row is
 * that event's standings; a null-event_id row (season_id set) is the
 * season-wide Overall Score standing used by the public leaderboard and
 * the "Compute Cumulative Standings" use case.
 *
 * One deliberate deviation from the literal ERD column list: standings
 * are keyed by `institute_id`, not `participant_id`. Each event has its
 * *own* Participant row per institute (see participants migration), so
 * there is no single participant_id that represents "TCGC-CAS" across
 * an entire season — only per event. Keying the season-overall row by
 * institute_id is what actually lets Level 3 aggregate one institute's
 * Overall Score across many events. `participant_id` is kept, nullable,
 * purely as an optional drill-down link to that event's specific
 * participant row (always set for event-scoped rows, always null for
 * season-overall rows).
 */
return new class extends Migration
{
    public function up(): void
{
    Schema::create('leaderboard_snapshots', function (Blueprint $table) {
        $table->id('snapshot_id');
        $table->unsignedBigInteger('season_id');
        $table->unsignedBigInteger('event_id')->nullable();   // no FK
        $table->unsignedBigInteger('institute_id');
        $table->unsignedBigInteger('participant_id')->nullable();
        $table->decimal('computed_score', 10, 4);
        $table->unsignedInteger('rank')->nullable();
        $table->boolean('is_visible')->default(false);
        $table->timestamp('updated_at')->nullable();
        $table->timestamp('created_at')->nullable();

        $table->unsignedBigInteger('event_scope_key')->storedAs('COALESCE(event_id, 0)');
        $table->unique(['season_id', 'event_scope_key', 'institute_id'], 'snapshot_scope_unique');

        // Keep the other foreign keys
        $table->foreign('season_id')->references('id')->on('seasons')->cascadeOnDelete();
        $table->foreign('institute_id')->references('id')->on('institutes')->cascadeOnDelete();
        $table->foreign('participant_id')->references('participant_id')->on('participants')->cascadeOnDelete();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
    }
};
