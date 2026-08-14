<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thesis Ch.4.4.1: "For rank-based scoring, the event rank earned at
 * Level 2 is converted to a point value using a configured conversion
 * table before contributing to Level 3." This is that table, configured
 * per event by the Organizer when scoring_method = rank_based. Any rank
 * not listed (e.g. 5th onward, if only 1st–4th are configured) converts
 * to 0 points.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_point_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->unsignedInteger('rank_position');
            $table->decimal('points', 8, 2);
            $table->timestamps();

            $table->unique(['event_id', 'rank_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_point_configs');
    }
};
