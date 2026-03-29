<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_scep_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->string('transaction_id')->index();
            $table->unsignedSmallInteger('message_type');
            $table->unsignedSmallInteger('status')->default(3); // PENDING
            $table->string('sender_nonce')->nullable();
            $table->string('recipient_nonce')->nullable();
            $table->text('csr_pem')->nullable();
            $table->foreignId('certificate_id')
                ->nullable()
                ->constrained('ca_certificates')
                ->nullOnDelete();
            $table->string('challenge_password')->nullable();
            $table->string('device_identifier')->nullable();
            $table->text('error_info')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['ca_id', 'transaction_id']);
            $table->index(['ca_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_scep_transactions');
    }
};
