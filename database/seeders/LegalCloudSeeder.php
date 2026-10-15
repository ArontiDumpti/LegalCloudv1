<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\ConsultaRag;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Expediente;
use App\Models\Notificacion;
use App\Models\Role;
use App\Models\User;
use App\Support\DocumentosDemo;
use App\Support\PdfSimple;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LegalCloudSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('documentos');

        $roles = [
            ['clave' => 'administrador', 'nombre' => 'Administrador', 'descripcion' => 'Gestiona usuarios, roles, configuración y auditoría.'],
            ['clave' => 'responsable_documental', 'nombre' => 'Responsable documental', 'descripcion' => 'Administra expedientes, categorías, versiones y el acervo.'],
            ['clave' => 'especialista', 'nombre' => 'Especialista / Analista', 'descripcion' => 'Clasifica documentos, revisa OCR y elabora análisis.'],
            ['clave' => 'consultor', 'nombre' => 'Usuario consultor', 'descripcion' => 'Consulta expedientes, busca y pregunta al asistente RAG.'],
        ];

        foreach ($roles as $rol) {
            Role::updateOrCreate(['clave' => $rol['clave']], $rol);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@legalcloud.com'],
            ['name' => 'Armas López Tatiana de Fátima Lucía', 'password' => Hash::make('password'), 'role' => 'administrador', 'cargo' => 'Administradora', 'activo' => true, 'email_verified_at' => now()]
        );
        $documental = User::updateOrCreate(
            ['email' => 'documental@legalcloud.com'],
            ['name' => 'Arteaga Benites Almendra Nicolle', 'password' => Hash::make('password'), 'role' => 'responsable_documental', 'cargo' => 'Responsable documental', 'activo' => true, 'email_verified_at' => now()]
        );
        $analista = User::updateOrCreate(
            ['email' => 'analista@legalcloud.com'],
            ['name' => 'Domínguez Alba Carlos Daniel', 'password' => Hash::make('password'), 'role' => 'especialista', 'cargo' => 'Especialista / Analista', 'activo' => true, 'email_verified_at' => now()]
        );
        $consultor = User::updateOrCreate(
            ['email' => 'consultor@legalcloud.com'],
            ['name' => 'Lozano Díaz Omar Jack', 'password' => Hash::make('password'), 'role' => 'consultor', 'cargo' => 'Usuario consultor', 'activo' => true, 'email_verified_at' => now()]
        );
        User::updateOrCreate(
            ['email' => 'williams@legalcloud.com'],
            ['name' => 'Segura Venturo Williams Aaron', 'password' => Hash::make('password'), 'role' => 'administrador', 'cargo' => 'Administrador', 'activo' => true, 'email_verified_at' => now()]
        );

        $categorias = [
            ['nombre' => 'Reglamento', 'slug' => 'reglamento', 'descripcion' => 'Normas internas de carácter general.', 'color' => '#1D4ED8'],
            ['nombre' => 'Resolución', 'slug' => 'resolucion', 'descripcion' => 'Actos administrativos de carácter particular o general.', 'color' => '#2563EB'],
            ['nombre' => 'Circular', 'slug' => 'circular', 'descripcion' => 'Instrucciones operativas a las dependencias.', 'color' => '#3B82F6'],
            ['nombre' => 'Expediente administrativo', 'slug' => 'expediente-administrativo', 'descripcion' => 'Carpetas de trámites en curso o cerrados.', 'color' => '#0EA5E9'],
            ['nombre' => 'Contrato', 'slug' => 'contrato', 'descripcion' => 'Instrumentos contractuales y anexos.', 'color' => '#0284C7'],
        ];

        $categoriaModelos = [];
        foreach ($categorias as $categoria) {
            $categoriaModelos[$categoria['slug']] = Categoria::updateOrCreate(['slug' => $categoria['slug']], $categoria);
        }

        $expedienteNormativo = Expediente::updateOrCreate(
            ['codigo' => 'EXP-2024-0187'],
            [
                'titulo' => 'Marco normativo de trámites administrativos',
                'descripcion' => 'Expediente maestro con el reglamento de trámites y las resoluciones que lo complementan. Es el documento de ejemplo para el asistente.',
                'estado' => 'abierto',
                'prioridad' => 'alta',
                'categoria_id' => $categoriaModelos['reglamento']->id,
                'responsable_id' => $documental->id,
                'creado_por' => $admin->id,
                'fecha_apertura' => '2024-03-12',
            ]
        );

        $expedienteLicencia = Expediente::updateOrCreate(
            ['codigo' => 'EXP-2026-0041'],
            [
                'titulo' => 'Solicitud de licencia de funcionamiento — Cooperativa Andes',
                'descripcion' => 'Trámite de licencia con documentos de soporte y seguimiento de estado.',
                'estado' => 'en_revision',
                'prioridad' => 'media',
                'categoria_id' => $categoriaModelos['expediente-administrativo']->id,
                'responsable_id' => $analista->id,
                'creado_por' => $documental->id,
                'fecha_apertura' => '2026-08-04',
            ]
        );

        $textoReglamento = DocumentosDemo::reglamentoTramites();
        $textoResolucion = DocumentosDemo::resolucionPlazos();
        $textoCircular = DocumentosDemo::circularClasificacion();
        $textoSolicitud = DocumentosDemo::solicitudLicencia();
        $textoContrato = DocumentosDemo::contratoSoporte();

        $rutaReglamento = $this->guardarPdf('RES-2024-0187-reglamento-tramites.pdf', 'Reglamento General de Trámites Administrativos', $textoReglamento, true);
        $rutaResolucion = $this->guardarPdf('RES-2025-0042-directrices-plazos.pdf', 'Directrices complementarias sobre plazos y subsanación', $textoResolucion);
        $rutaCircular = $this->guardarPdf('CIR-2026-0008-clasificacion-documentos.pdf', 'Circular de clasificación y carga de documentos', $textoCircular);
        $rutaSolicitud = $this->guardarPdf('SOL-2026-0041-cooperativa-andes.pdf', 'Formulario de solicitud de licencia — Cooperativa Andes', $textoSolicitud);
        $rutaContrato = $this->guardarPdf('CT-2026-015-gestion-documental.pdf', 'Contrato de prestación de servicios de gestión documental', $textoContrato);

        $reglamento = Documento::updateOrCreate(
            ['codigo' => 'DOC-RES-2024-0187'],
            [
                'expediente_id' => $expedienteNormativo->id,
                'categoria_id' => $categoriaModelos['reglamento']->id,
                'titulo' => 'Reglamento General de Trámites Administrativos',
                'tipo' => 'reglamento',
                'descripcion' => 'PDF de ejemplo con los requisitos del artículo 12. Descargable y listo para volver a subir.',
                'archivo_nombre' => 'RES-2024-0187-reglamento-tramites.pdf',
                'archivo_ruta' => $rutaReglamento,
                'mime' => 'application/pdf',
                'tamano' => Storage::disk('public')->size($rutaReglamento),
                'version_actual' => 2,
                'ocr_estado' => 'procesado',
                'contenido_ocr' => $textoReglamento,
                'embedding_estado' => 'indexado',
                'subido_por' => $documental->id,
            ]
        );

        $resolucion = Documento::updateOrCreate(
            ['codigo' => 'DOC-RES-2025-0042'],
            [
                'expediente_id' => $expedienteNormativo->id,
                'categoria_id' => $categoriaModelos['resolucion']->id,
                'titulo' => 'Directrices complementarias sobre plazos y subsanación',
                'tipo' => 'resolucion',
                'descripcion' => 'PDF que complementa el reglamento en plazos, notificaciones y consulta del acervo.',
                'archivo_nombre' => 'RES-2025-0042-directrices-plazos.pdf',
                'archivo_ruta' => $rutaResolucion,
                'mime' => 'application/pdf',
                'tamano' => Storage::disk('public')->size($rutaResolucion),
                'version_actual' => 1,
                'ocr_estado' => 'procesado',
                'contenido_ocr' => $textoResolucion,
                'embedding_estado' => 'indexado',
                'subido_por' => $analista->id,
            ]
        );

        Documento::updateOrCreate(
            ['codigo' => 'DOC-CIR-2026-0008'],
            [
                'expediente_id' => $expedienteNormativo->id,
                'categoria_id' => $categoriaModelos['circular']->id,
                'titulo' => 'Circular de clasificación y carga de documentos',
                'tipo' => 'circular',
                'descripcion' => 'Instrucción operativa para subir PDF, versionar y consultar el acervo.',
                'archivo_nombre' => 'CIR-2026-0008-clasificacion-documentos.pdf',
                'archivo_ruta' => $rutaCircular,
                'mime' => 'application/pdf',
                'tamano' => Storage::disk('public')->size($rutaCircular),
                'version_actual' => 1,
                'ocr_estado' => 'procesado',
                'contenido_ocr' => $textoCircular,
                'embedding_estado' => 'indexado',
                'subido_por' => $documental->id,
            ]
        );

        Documento::updateOrCreate(
            ['codigo' => 'DOC-SOL-2026-0041'],
            [
                'expediente_id' => $expedienteLicencia->id,
                'categoria_id' => $categoriaModelos['expediente-administrativo']->id,
                'titulo' => 'Formulario de solicitud de licencia — Cooperativa Andes',
                'tipo' => 'formulario',
                'descripcion' => 'PDF de solicitud. Puede usarse para probar el OCR simulado.',
                'archivo_nombre' => 'SOL-2026-0041-cooperativa-andes.pdf',
                'archivo_ruta' => $rutaSolicitud,
                'mime' => 'application/pdf',
                'tamano' => Storage::disk('public')->size($rutaSolicitud),
                'version_actual' => 1,
                'ocr_estado' => 'pendiente',
                'contenido_ocr' => null,
                'embedding_estado' => 'pendiente',
                'subido_por' => $consultor->id,
            ]
        );

        Documento::updateOrCreate(
            ['codigo' => 'DOC-CT-2026-015'],
            [
                'expediente_id' => $expedienteLicencia->id,
                'categoria_id' => $categoriaModelos['contrato']->id,
                'titulo' => 'Contrato de prestación de servicios de gestión documental',
                'tipo' => 'contrato',
                'descripcion' => 'PDF contractual de ejemplo asociado al expediente de la cooperativa.',
                'archivo_nombre' => 'CT-2026-015-gestion-documental.pdf',
                'archivo_ruta' => $rutaContrato,
                'mime' => 'application/pdf',
                'tamano' => Storage::disk('public')->size($rutaContrato),
                'version_actual' => 1,
                'ocr_estado' => 'procesado',
                'contenido_ocr' => $textoContrato,
                'embedding_estado' => 'indexado',
                'subido_por' => $analista->id,
            ]
        );

        DocumentoVersion::query()->where('documento_id', $reglamento->id)->delete();
        DocumentoVersion::create([
            'documento_id' => $reglamento->id,
            'numero' => 1,
            'resumen_cambios' => 'Carga inicial del reglamento.',
            'contenido' => $textoReglamento,
            'subido_por' => $documental->id,
            'created_at' => now()->subMonths(6),
        ]);
        DocumentoVersion::create([
            'documento_id' => $reglamento->id,
            'numero' => 2,
            'resumen_cambios' => 'Se incorpora el parágrafo de subsanación en el artículo 12.',
            'contenido' => $textoReglamento,
            'subido_por' => $analista->id,
        ]);

        DocumentoVersion::query()->where('documento_id', $resolucion->id)->delete();
        DocumentoVersion::create([
            'documento_id' => $resolucion->id,
            'numero' => 1,
            'resumen_cambios' => 'Publicación original.',
            'contenido' => $textoResolucion,
            'subido_por' => $analista->id,
        ]);

        ConsultaRag::query()->delete();
        ConsultaRag::create([
            'user_id' => $consultor->id,
            'pregunta' => '¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?',
            'respuesta' => 'Según el Reglamento General de Trámites Administrativos (Resolución N.º 187-2024), los requisitos incluyen solicitud escrita, identificación, formulario oficial, soportes, comprobante de pago cuando aplique, poder si actúa un tercero, y datos de notificación.',
            'fuentes' => [[
                'documento_id' => $reglamento->id,
                'codigo' => $reglamento->codigo,
                'titulo' => $reglamento->titulo,
                'expediente' => $expedienteNormativo->codigo,
                'texto' => 'Artículo 12. Requisitos para iniciar un procedimiento administrativo.',
                'puntaje' => 12,
            ]],
            'modelo_simulado' => 'LegalCloud RAG (demo, sin LLM)',
            'created_at' => now()->subHours(2),
        ]);

        foreach ([$admin, $documental, $analista, $consultor] as $usuario) {
            Notificacion::updateOrCreate(
                ['user_id' => $usuario->id, 'titulo' => 'Documento de ejemplo listo'],
                [
                    'mensaje' => 'El reglamento RES-2024-0187 ya está procesado con OCR e indexado para consultas de ejemplo.',
                    'tipo' => 'exito',
                    'leida' => false,
                    'enlace' => '/documentos/'.$reglamento->id,
                ]
            );
        }

        Notificacion::updateOrCreate(
            ['user_id' => $analista->id, 'titulo' => 'OCR pendiente'],
            [
                'mensaje' => 'El formulario DOC-SOL-2026-0041 aún no tiene texto extraído.',
                'tipo' => 'alerta',
                'leida' => false,
                'enlace' => '/ocr',
            ]
        );

        Auditoria::query()->delete();
        Auditoria::insert([
            ['user_id' => $admin->id, 'accion' => 'alta_usuario', 'modulo' => 'usuarios', 'descripcion' => 'Se crearon los cuatro perfiles de demostración.', 'ip' => '127.0.0.1', 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['user_id' => $documental->id, 'accion' => 'carga_documento', 'modulo' => 'documentos', 'descripcion' => 'Carga de RES-2024-0187 Reglamento General de Trámites.', 'ip' => '127.0.0.1', 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['user_id' => $analista->id, 'accion' => 'ocr_procesado', 'modulo' => 'ocr', 'descripcion' => 'OCR simulado sobre el reglamento de trámites.', 'ip' => '127.0.0.1', 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['user_id' => $consultor->id, 'accion' => 'consulta_rag', 'modulo' => 'asistente', 'descripcion' => 'Consulta de ejemplo sobre requisitos del procedimiento.', 'ip' => '127.0.0.1', 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
        ]);
    }

    private function guardarPdf(string $nombre, string $titulo, string $texto, bool $publico = false): string
    {
        $pdf = PdfSimple::generar($titulo, $texto);
        $ruta = 'documentos/'.$nombre;
        Storage::disk('public')->put($ruta, $pdf);

        if ($publico) {
            File::ensureDirectoryExists(public_path('ejemplos'));
            File::put(public_path('ejemplos/'.$nombre), $pdf);
        }

        return $ruta;
    }
}
