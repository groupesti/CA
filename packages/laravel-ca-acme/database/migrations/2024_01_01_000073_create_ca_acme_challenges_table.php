<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_acme_challenges', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('authorization_id');
            $table->string('type');
            $table->string('status')->default('pending');
            $table->string('token')->unique();
            $table->string('key_authorization')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->json('error')->nullable();
            $table->timestamps();

            $table->foreign('authorization_id')
                ->references('id')
                ->on('ca_acme_authorizations')
                ->onDelete('cascade');

            $table->index(['authorization_id', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_acme_challenges');
    }
};
