<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master list of TCGC's competing institutes. The ERD's PARTICIPANT
 * entity stores `institute_name` inline per-event; LuxTab normalizes
 * that into this master table (see `participants` migration) so an
 * institute's name/colors/logo are defined once and reused across every
 * event and season instead of re-typed each time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('color_hex', 7)->default('#1c46f5');
            $table->string('logo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
