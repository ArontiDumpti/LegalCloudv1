<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fragmentos_vector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->longText('texto');
            $table->json('embedding');
            $table->string('modelo')->default('gemini-embedding-001');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fragmentos_vector');
    }
};
