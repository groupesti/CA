<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_acme_nonces', function (Blueprint $table): void {
            $table->id();
            $table->string('nonce')->unique();
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);

            $table->index(['used', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_acme_nonces');
    }
};
