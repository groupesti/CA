<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_tsa_certificates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('ca_id');
            $table->uuid('certificate_id');
            $table->uuid('key_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['ca_id', 'is_active']);

            $table->foreign('ca_id')
                ->references('id')
                ->on('certificate_authorities')
                ->cascadeOnDelete();

            $table->foreign('certificate_id')
                ->references('id')
                ->on('ca_certificates')
                ->cascadeOnDelete();

            $table->foreign('key_id')
                ->references('id')
                ->on('ca_keys')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_tsa_certificates');
    }
};
