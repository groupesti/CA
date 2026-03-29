<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_certificate_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('certificate_authority_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('type', 50);
            $table->json('key_usage')->nullable();
            $table->json('extended_key_usage')->nullable();
            $table->json('basic_constraints')->nullable();
            $table->json('subject_rules')->nullable();
            $table->json('san_types')->nullable();
            $table->json('allowed_key_types')->nullable();
            $table->integer('validity_days')->default(365);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('certificate_authority_id')
                ->references('id')
                ->on('certificate_authorities')
                ->onDelete('cascade');

            $table->index(['certificate_authority_id', 'is_active']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_certificate_templates');
    }
};
