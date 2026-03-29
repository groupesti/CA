<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->nullable()->index();
            $table->string('action', 255);
            $table->string('subject_type', 255);
            $table->string('subject_id', 36);
            $table->string('actor_type', 255)->nullable();
            $table->string('actor_id', 36)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('performed_at');

            $table->index(['subject_type', 'subject_id']);
            $table->index(['actor_type', 'actor_id']);
            $table->index('action');
            $table->index('performed_at');
            $table->index(['tenant_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_audit_logs');
    }
};
