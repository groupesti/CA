<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_ocsp_responder_certificates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->uuid('tenant_id')->nullable()->index();
            $table->foreignUuid('certificate_id')
                ->constrained('ca_certificates')
                ->cascadeOnDelete();
            $table->foreignUuid('key_id')
                ->constrained('ca_keys')
                ->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['ca_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_ocsp_responder_certificates');
    }
};
