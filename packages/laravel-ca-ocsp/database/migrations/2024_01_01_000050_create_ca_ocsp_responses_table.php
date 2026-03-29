<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_ocsp_responses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('certificate_serial', 128);
            $table->string('status', 20); // good, revoked, unknown
            $table->timestamp('this_update');
            $table->timestamp('next_update')->nullable();
            $table->binary('response_der')->nullable();
            $table->timestamp('revocation_time')->nullable();
            $table->unsignedSmallInteger('revocation_reason')->nullable();
            $table->timestamps();

            $table->index(['ca_id', 'certificate_serial']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_ocsp_responses');
    }
};
