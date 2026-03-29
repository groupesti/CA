<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ca_est_enrollments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('ca_id');
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('type', 20)->index(); // enroll, reenroll, serverkeygen
            $table->string('status', 20)->default('pending')->index(); // pending, completed, failed, revoked
            $table->string('client_identity')->nullable();
            $table->text('csr_pem');
            $table->uuid('certificate_id')->nullable();
            $table->uuid('key_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('ca_id')
                ->references('id')
                ->on('certificate_authorities')
                ->onDelete('cascade');

            $table->foreign('certificate_id')
                ->references('id')
                ->on('ca_certificates')
                ->onDelete('set null');

            $table->foreign('key_id')
                ->references('id')
                ->on('ca_keys')
                ->onDelete('set null');

            $table->index(['ca_id', 'type', 'status']);
            $table->index(['ca_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_est_enrollments');
    }
};
