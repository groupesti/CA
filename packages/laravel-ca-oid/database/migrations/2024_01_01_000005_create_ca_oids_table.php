<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_oids', function (Blueprint $table) {
            $table->id();
            $table->string('oid')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->index();
            $table->boolean('is_standard')->default(true);
            $table->string('parent_oid')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_oids');
    }
};
