<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_certificates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('key_id')
                ->constrained('ca_keys')
                ->cascadeOnDelete();
            $table->foreignId('csr_id')
                ->nullable()
                ->constrained('ca_csrs')
                ->nullOnDelete();
            $table->foreignId('issuer_certificate_id')
                ->nullable()
                ->constrained('ca_certificates')
                ->nullOnDelete();
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('ca_certificate_templates')
                ->nullOnDelete();
            $table->string('serial_number', 255);
            $table->string('type', 50);
            $table->json('subject_dn');
            $table->json('san')->nullable();
            $table->text('certificate_pem');
            $table->binary('certificate_der')->nullable();
            $table->string('fingerprint_sha256', 95)->unique();
            $table->string('status', 30)->default('active');
            $table->string('revocation_reason', 100)->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('not_before');
            $table->timestamp('not_after');
            $table->json('key_usage')->nullable();
            $table->json('extended_key_usage')->nullable();
            $table->string('ms_template_oid')->nullable();
            $table->string('ms_template_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ca_id', 'serial_number']);
            $table->index(['ca_id', 'status']);
            $table->index('not_after');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_certificates');
    }
};
