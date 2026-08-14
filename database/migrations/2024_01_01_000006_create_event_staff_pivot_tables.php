<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supplementary Tabulator/Tournament Manager assignments beyond the
 * single `events.tabulator_id` / event owner. Kept from the previous
 * build per documented adviser feedback that one event may need more
 * than one Tabulator or TM; layered on top of the ERD rather than
 * replacing its 1:M organizer/tabulator relationship.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_tabulators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });

        Schema::create('event_tournament_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tournament_managers');
        Schema::dropIfExists('event_tabulators');
    }
};
