<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_name_constraints', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('policy_id')
                ->constrained('ca_certificate_policies')
                ->cascadeOnDelete();
            $table->foreignUuid('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->string('type'); // 'permitted' or 'excluded'
            $table->string('name_type'); // dns, email, ip, uri, directoryName
            $table->string('value'); // e.g., '.example.com', '10.0.0.0/8'
            $table->integer('min_subtree')->default(0);
            $table->integer('max_subtree')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['ca_id', 'type', 'name_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_name_constraints');
    }
};
