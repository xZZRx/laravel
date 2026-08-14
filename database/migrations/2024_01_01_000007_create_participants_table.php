<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: PARTICIPANT(participant_id PK, event_id FK, institute_name).
 * `institute_id` links back to the institutes master table (see
 * institutes migration) so the display name/colors stay in sync
 * automatically for institute-based participation; `name` is a plain
 * text fallback for team/individual participation types.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id('participant_id');
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('institute_id')->nullable()->constrained('institutes')->cascadeOnDelete();
            $table->string('name'); // display name; mirrors institute name when institute-based
            $table->boolean('is_disqualified')->default(false);
            $table->timestamps();

            $table->unique(['event_id', 'institute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
