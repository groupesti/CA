<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_certificate_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('policy_oid');
            $table->string('cps_uri')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['ca_id', 'policy_oid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_certificate_policies');
    }
};
