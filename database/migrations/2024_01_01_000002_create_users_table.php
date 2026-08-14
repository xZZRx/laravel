<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: USER(user_id PK, name, email, password, role, is_active, created_at)
 *
 * `role` is the ERD's ENUM(Admin, Organizer, Tournament Manager, Judge,
 * Tabulator, Viewer) — stored as a string enum via the Role enum in
 * App\Enums\Role rather than a native MySQL ENUM column, so new roles
 * never require a migration. `username` and `institute_id` are additive:
 * the thesis's Use Case 1 (Authenticate/Login) refers to logging in with
 * an identifier and password, and TCGC staff generally log in with a
 * short username rather than an email, so both are supported.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 40)->default('viewer')->index();
            $table->foreignId('institute_id')->nullable()->constrained('institutes')->nullOnDelete();
            $table->string('avatar_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Database-backed sessions double as the "who is active right now"
        // signal the Architecture diagram's Concurrency Management panel
        // and Ch.4's optimistic-locking requirement rely on.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
