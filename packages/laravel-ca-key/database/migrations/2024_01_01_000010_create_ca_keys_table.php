<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_keys', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->foreignId('ca_id')
                ->nullable()
                ->constrained('certificate_authorities')
                ->nullOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->string('algorithm', 50);
            $table->json('parameters');
            $table->text('public_key_pem');
            $table->text('private_key_encrypted');
            $table->string('encryption_strategy', 20)->default('laravel');
            $table->string('fingerprint_sha256', 95)->unique();
            $table->string('status', 20)->default('active');
            $table->string('usage', 50)->default('certificate');
            $table->string('storage_path', 2048)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_keys');
    }
};
