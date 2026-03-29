<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_acme_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('ca_id');
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('status')->default('valid');
            $table->json('contact');
            $table->json('public_key_jwk');
            $table->string('account_key_thumbprint')->unique();
            $table->boolean('terms_agreed')->default(false);
            $table->string('external_account_id')->nullable();
            $table->timestamps();

            $table->foreign('ca_id')
                ->references('id')
                ->on('certificate_authorities')
                ->onDelete('cascade');

            $table->index(['ca_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_acme_accounts');
    }
};
