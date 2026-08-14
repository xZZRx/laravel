<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Not shown as its own ERD box, but required so LMC 2026 and LMC 2027
 * (or ARSO 2026 and ARSO 2027) don't collide in the same standings.
 * The Level-3 "Overall Score" and its leaderboard snapshot are always
 * computed within one season. Only one season per program is
 * `is_active` at a time — that's the season new events attach to and
 * the one the public leaderboard defaults to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('program', 10); // 'lmc' | 'arso'
            $table->unsignedSmallInteger('year');
            $table->string('label');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['program', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
