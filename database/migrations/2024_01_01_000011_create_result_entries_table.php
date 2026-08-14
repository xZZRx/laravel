<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: RESULT_ENTRY(result_id PK, event_id FK, tabulator_id FK,
 * participant_id FK, overall_score, rank, entered_at).
 *
 * Used for `scoring_type = by_round` events, where there is no judge
 * panel and the Tabulator enters each participant's result directly
 * (e.g. from bracket/match outcomes). `score_text` is an additive
 * convenience for non-numeric results ("Champion", "1:32.04") that
 * still resolve to a numeric `overall_score` for ranking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_entries', function (Blueprint $table) {
            $table->id('result_id');
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('tabulator_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->foreignId('participant_id')->constrained('participants', 'participant_id')->cascadeOnDelete();
            $table->decimal('overall_score', 10, 4);
            $table->string('score_text')->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_entries');
    }
};
