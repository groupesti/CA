<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_policy_constraints', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('policy_id')
                ->constrained('ca_certificate_policies')
                ->cascadeOnDelete();
            $table->foreignUuid('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->string('constraint_type'); // require_explicit_policy, inhibit_policy_mapping, inhibit_any_policy
            $table->integer('skip_certs')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['ca_id', 'constraint_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_policy_constraints');
    }
};
