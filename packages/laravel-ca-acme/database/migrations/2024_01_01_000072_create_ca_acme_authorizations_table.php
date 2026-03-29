<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_acme_authorizations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->string('identifier_type');
            $table->string('identifier_value');
            $table->string('status')->default('pending');
            $table->boolean('wildcard')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('ca_acme_orders')
                ->onDelete('cascade');

            $table->index(['order_id', 'status']);
            $table->index(['identifier_type', 'identifier_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_acme_authorizations');
    }
};
