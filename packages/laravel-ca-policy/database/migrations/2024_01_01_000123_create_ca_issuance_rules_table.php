<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_issuance_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->foreignUuid('policy_id')
                ->nullable()
                ->constrained('ca_certificate_policies')
                ->nullOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('rule_class'); // FQCN of PolicyRuleInterface implementation
            $table->json('parameters')->nullable();
            $table->integer('priority')->default(0);
            $table->string('severity')->default('error'); // error, warning, info
            $table->string('action_on_failure')->default('deny'); // allow, deny, require_approval
            $table->boolean('enabled')->default(true);
            $table->json('applies_to_types')->nullable(); // array of CertificateType values, null = all
            $table->timestamps();

            $table->index(['ca_id', 'enabled', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_issuance_rules');
    }
};
