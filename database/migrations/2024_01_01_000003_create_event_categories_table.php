<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thesis Ch.1.1 / Ch.4.4.1 Level 3: "the system applies the configured
 * category weights — Academic Events at 30%, Special Events at 50%, and
 * Socio-Cultural Events at 20% — to compute the Overall Score." That
 * requires categories to actually carry a weight, which is the field the
 * previous LMC-only build was missing entirely (it only had Academic /
 * Special / Sports as unweighted labels used for filtering).
 *
 * `program` distinguishes the Luxmundis Cup from the ARSO Festival,
 * since each runs its own category set and weighting scheme. Weights
 * are per-program-and-category rather than hard-coded so an Organizer
 * can correct them from Settings without a code change (the SSC-
 * confirmed LMC split is seeded as an editable placeholder — see the
 * seeder notes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('program', 10); // 'lmc' | 'arso'
            $table->string('name');
            $table->string('slug', 40);
            $table->decimal('weight_pct', 5, 2)->default(0);
            $table->string('icon', 40)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['program', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
