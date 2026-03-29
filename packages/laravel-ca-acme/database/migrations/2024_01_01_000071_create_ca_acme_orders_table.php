<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_acme_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('ca_id');
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('status')->default('pending');
            $table->json('identifiers');
            $table->timestamp('not_before')->nullable();
            $table->timestamp('not_after')->nullable();
            $table->uuid('certificate_id')->nullable();
            $table->text('finalize_csr')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('ca_acme_accounts')
                ->onDelete('cascade');

            $table->foreign('ca_id')
                ->references('id')
                ->on('certificate_authorities')
                ->onDelete('cascade');

            $table->foreign('certificate_id')
                ->references('id')
                ->on('ca_certificates')
                ->onDelete('set null');

            $table->index(['account_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_acme_orders');
    }
};
