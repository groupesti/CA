<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_certificate_chains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('certificate_id')
                ->constrained('ca_certificates')
                ->cascadeOnDelete();
            $table->foreignId('parent_certificate_id')
                ->constrained('ca_certificates')
                ->cascadeOnDelete();
            $table->unsignedInteger('depth');
            $table->timestamps();

            $table->unique(['certificate_id', 'parent_certificate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_certificate_chains');
    }
};
