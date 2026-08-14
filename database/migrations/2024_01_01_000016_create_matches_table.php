<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: MATCH(match_id PK, tournament_id FK, round_number,
 * participant_a_id FK, participant_b_id FK, winner_id FK, score_a,
 * score_b, status, match_date, venue).
 *
 * `status` is restricted to the thesis's exact three values (scheduled,
 * ongoing, completed — Module 3 / Use Case 5) rather than the previous
 * build's larger set. `notes` doubles as the forfeiture explanation
 * when a match ends without play. `match_number`, `is_bye`,
 * `next_match_id` and `next_match_slot` are implementation necessities
 * for actually generating and auto-advancing a bracket — additive
 * detail, not a contradiction of the diagram. `rescheduled_from` /
 * `reschedule_reason` support Use Case 5's "Scheduling Conflict" flow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id('match_id');
            $table->foreignId('tournament_id')->constrained('tournaments', 'tournament_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->unsignedInteger('match_number');
            $table->foreignId('participant_a_id')->nullable()->constrained('participants', 'participant_id')->nullOnDelete();
            $table->foreignId('participant_b_id')->nullable()->constrained('participants', 'participant_id')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('participants', 'participant_id')->nullOnDelete();
            $table->decimal('score_a', 8, 2)->nullable();
            $table->decimal('score_b', 8, 2)->nullable();
            $table->string('score_a_text')->nullable();
            $table->string('score_b_text')->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled | ongoing | completed
            $table->dateTime('match_date')->nullable();
            $table->string('venue')->nullable();
            $table->dateTime('rescheduled_from')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->boolean('is_bye')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('next_match_id')->nullable();
            $table->string('next_match_slot', 1)->nullable(); // 'a' | 'b'
            $table->timestamps();

            $table->unique(['tournament_id', 'round_number', 'match_number']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreign('next_match_id')->references('match_id')->on('matches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['next_match_id']);
        });
        Schema::dropIfExists('matches');
    }
};
