<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_tsa_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('ca_id');
            $table->uuid('tenant_id')->nullable();
            $table->string('serial_number', 128);
            $table->string('hash_algorithm', 32);
            $table->string('message_imprint', 256);
            $table->string('policy_oid', 128);
            $table->timestamp('gen_time', 6);
            $table->string('nonce', 128)->nullable();
            $table->json('accuracy')->nullable();
            $table->binary('tsr_der');
            $table->uuid('signing_certificate_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('serial_number');
            $table->index('gen_time');
            $table->index('ca_id');
            $table->index('tenant_id');

            $table->foreign('ca_id')
                ->references('id')
                ->on('certificate_authorities')
                ->cascadeOnDelete();

            $table->foreign('signing_certificate_id')
                ->references('id')
                ->on('ca_certificates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_tsa_tokens');
    }
};
