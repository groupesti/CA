<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_csrs', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignUuid('ca_id')
                ->nullable()
                ->constrained('certificate_authorities')
                ->nullOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('key_id')
                ->nullable()
                ->constrained('ca_keys')
                ->nullOnDelete();
            $table->foreignUuid('template_id')
                ->nullable()
                ->constrained('ca_certificate_templates')
                ->nullOnDelete();
            $table->json('subject_dn');
            $table->json('san')->nullable();
            $table->text('csr_pem');
            $table->string('status', 20)->default('pending')->index();
            $table->string('requested_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_csrs');
    }
};
