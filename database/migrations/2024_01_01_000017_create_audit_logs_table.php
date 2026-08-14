<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERD: AUDIT_LOG(log_id PK, user_id FK, action, target_type, target_id,
 * performed_at). Column names follow the ERD exactly; `old_values` /
 * `new_values` / `ip_address` / `user_agent` are kept from the previous
 * build as additive detail the diagram doesn't show but Layer 3's
 * "Audit Logging: record all user actions and score modifications with
 * timestamps" calls for in substance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('action');
            $table->string('target_type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('performed_at')->useCurrent();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
