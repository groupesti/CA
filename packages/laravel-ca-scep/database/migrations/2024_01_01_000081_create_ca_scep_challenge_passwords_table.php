<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_scep_challenge_passwords', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ca_id')
                ->constrained('certificate_authorities')
                ->cascadeOnDelete();
            $table->string('password_hash', 64);
            $table->string('purpose')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('created_at');

            $table->index(['ca_id', 'password_hash']);
            $table->index(['ca_id', 'used', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_scep_challenge_passwords');
    }
};
