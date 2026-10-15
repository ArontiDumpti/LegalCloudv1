<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->string('color', 20)->default('#1D4ED8');
            $table->timestamps();
        });

        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('abierto');
            $table->string('prioridad')->default('media');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_apertura')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->timestamps();
        });

        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('codigo')->unique();
            $table->string('titulo');
            $table->string('tipo')->default('otro');
            $table->text('descripcion')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_ruta')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('tamano')->default(0);
            $table->unsignedInteger('version_actual')->default(1);
            $table->string('ocr_estado')->default('pendiente');
            $table->longText('contenido_ocr')->nullable();
            $table->string('embedding_estado')->default('pendiente');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('documento_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->unsignedInteger('numero');
            $table->string('resumen_cambios')->nullable();
            $table->longText('contenido')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('consultas_rag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('pregunta');
            $table->longText('respuesta');
            $table->json('fuentes')->nullable();
            $table->string('modelo_simulado')->default('LegalCloud RAG (demo)');
            $table->timestamps();
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('tipo')->default('info');
            $table->boolean('leida')->default(false);
            $table->string('enlace')->nullable();
            $table->timestamps();
        });

        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion');
            $table->string('modulo');
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('consultas_rag');
        Schema::dropIfExists('documento_versiones');
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('roles');
    }
};
