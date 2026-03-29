<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_authorities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->nullable()->index();
            $table->uuid('parent_id')->nullable();
            $table->string('type', 50);
            $table->string('status', 50)->default('active');
            $table->json('subject_dn');
            $table->string('serial_number', 100)->unique();
            $table->string('key_algorithm', 50);
            $table->string('hash_algorithm', 50)->default('sha256');
            $table->integer('path_length')->nullable();
            $table->timestamp('not_before')->nullable();
            $table->timestamp('not_after')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('certificate_authorities')
                ->onDelete('restrict');

            $table->index(['tenant_id', 'status']);
            $table->index(['parent_id', 'status']);
            $table->index('type');
            $table->index('not_after');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_authorities');
    }
};
