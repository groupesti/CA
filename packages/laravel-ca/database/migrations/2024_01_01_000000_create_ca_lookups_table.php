<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('slug', 100);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('numeric_value')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(true);
            $table->timestamps();

            $table->unique(['type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_lookups');
    }
};
