<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A judge panel seat for one event. `user_id` is set for judges who log
 * in and use the Judge Interface themselves (the ERD's JUDGE_SCORE_ENTRY
 * .judge_id -> USER case). `user_id` nullable + `name`/`title` supports
 * guest judges with no account, entered on their behalf by the
 * Tabulator — a practical addition beyond the ERD, kept because it's
 * common for TCGC to bring in an external judge for a single event.
 * If you want the system to match the diagram literally, drop this
 * nullability and require every judge to be a registered `judge` user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'event_id')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('name');
            $table->string('title')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judges');
    }
};
