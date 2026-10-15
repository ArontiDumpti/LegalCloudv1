<?php

namespace App\Support;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\ConsultaRag;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Expediente;
use App\Models\Notificacion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LegalCloudApi
{
    public const ROLES = [
        'administrador' => [
            'codigo' => 'ADMIN',
            'nombre' => 'Administrador',
            'permisos' => [
                'dashboard.leer', 'usuarios.leer', 'usuarios.invitar', 'usuarios.actualizar', 'usuarios.suspender',
                'roles.leer', 'roles.actualizar', 'categorias.leer', 'categorias.crear', 'categorias.actualizar',
                'expedientes.leer', 'expedientes.crear', 'expedientes.actualizar', 'expedientes.transicionar',
                'documentos.leer', 'documentos.crear', 'documentos.actualizar', 'documentos.descargar',
                'busqueda.consultar', 'asistente.conversar', 'resumenes.solicitar',
                'notificaciones.leer', 'auditoria.leer',
            ],
        ],
        'responsable_documental' => [
            'codigo' => 'RESPONSABLE_DOCUMENTAL',
            'nombre' => 'Responsable documental',
            'permisos' => [
                'dashboard.leer', 'usuarios.leer', 'roles.leer',
                'categorias.leer', 'categorias.crear', 'categorias.actualizar',
                'expedientes.leer', 'expedientes.crear', 'expedientes.actualizar', 'expedientes.transicionar',
                'documentos.leer', 'documentos.crear', 'documentos.actualizar', 'documentos.descargar',
                'busqueda.consultar', 'asistente.conversar', 'resumenes.solicitar',
                'notificaciones.leer', 'auditoria.leer',
            ],
        ],
        'especialista' => [
            'codigo' => 'ESPECIALISTA',
            'nombre' => 'Especialista / Analista',
            'permisos' => [
                'dashboard.leer', 'usuarios.leer', 'roles.leer', 'categorias.leer',
                'expedientes.leer', 'expedientes.actualizar',
                'documentos.leer', 'documentos.crear', 'documentos.actualizar', 'documentos.descargar',
                'busqueda.consultar', 'asistente.conversar', 'resumenes.solicitar',
                'notificaciones.leer', 'auditoria.leer',
            ],
        ],
        'consultor' => [
            'codigo' => 'CONSULTOR',
            'nombre' => 'Usuario consultor',
            'permisos' => [
                'dashboard.leer', 'categorias.leer',
                'expedientes.leer', 'documentos.leer', 'documentos.descargar',
                'busqueda.consultar', 'asistente.conversar',
                'notificaciones.leer',
            ],
        ],
    ];

    public static function uuid(int|string $id): string
    {
        return sprintf('00000000-0000-4000-8000-%012d', (int) $id);
    }

    public static function parseId(string $id): int
    {
        if (ctype_digit($id)) {
            return (int) $id;
        }

        if (preg_match('/-(\d{12})$/', $id, $coincidencias)) {
            return (int) $coincidencias[1];
        }

        abort(self::problema(404, 'No encontrado', 'Identificador inválido.'));
    }

    public static function pagina(Collection|array $datos, int $limit = 50): array
    {
        $items = $datos instanceof Collection ? $datos->values()->all() : array_values($datos);

        return [
            'datos' => $items,
            'paginacion' => [
                'limit' => $limit,
                'nextCursor' => null,
            ],
        ];
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $datos
     * @return array{datos: list<mixed>, paginacion: array{limit: int, pagina: int, total: int, paginas: int, nextCursor: string|null}}
     */
    public static function paginaNumerada(Collection|array $datos, int $pagina, int $limit, int $total): array
    {
        $items = $datos instanceof Collection ? $datos->values()->all() : array_values($datos);
        $paginas = max(1, (int) ceil($total / max($limit, 1)));

        return [
            'datos' => $items,
            'paginacion' => [
                'limit' => $limit,
                'pagina' => $pagina,
                'total' => $total,
                'paginas' => $paginas,
                'nextCursor' => $pagina < $paginas ? (string) ($pagina + 1) : null,
            ],
        ];
    }

    public static function problema(int $status, string $title, string $detail, ?array $errors = null): JsonResponse
    {
        $cuerpo = [
            'type' => 'https://legalcloud.example/problemas/'.$status,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => '/'.ltrim((string) request()?->path(), '/'),
        ];

        if ($errors) {
            $cuerpo['errors'] = $errors;
        }

        return response()->json($cuerpo, $status)
            ->header('Content-Type', 'application/problem+json');
    }

    public static function json(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status)
            ->header('X-Request-Id', request()?->header('X-Request-Id') ?: (string) str()->uuid());
    }

    public static function claveDesdeCodigo(string $codigo): ?string
    {
        foreach (self::ROLES as $clave => $rol) {
            if ($rol['codigo'] === strtoupper($codigo) || $clave === $codigo) {
                return $clave;
            }
        }

        return Role::query()->where('clave', $codigo)->value('clave');
    }

    public static function permisosDe(User $user): array
    {
        return self::ROLES[$user->role]['permisos'] ?? self::ROLES['consultor']['permisos'];
    }

    public static function exige(User $user, string $permiso): void
    {
        if (! in_array($permiso, self::permisosDe($user), true)) {
            throw new AccessDeniedHttpException('El rol no tiene el permiso '.$permiso.'.');
        }
    }

    public static function exigeSinConsultor(User $user): void
    {
        if ($user->esConsultor()) {
            throw new AccessDeniedHttpException('El consultor no puede acceder a usuarios, roles ni auditoría.');
        }
    }

    public static function usuario(User $user): array
    {
        $partes = preg_split('/\s+/', trim($user->name)) ?: [$user->name];
        $nombre = array_shift($partes) ?: $user->name;
        $apellido = implode(' ', $partes) ?: null;
        $rol = self::rolResumen($user->role);

        return [
            'id' => self::uuid($user->id),
            'correo' => $user->email,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'estado' => $user->activo ? 'activo' : 'suspendido',
            'roles' => $rol ? [$rol] : [],
            'creadoEn' => optional($user->created_at)?->toIso8601String(),
            'actualizadoEn' => optional($user->updated_at)?->toIso8601String(),
        ];
    }

    public static function perfil(User $user): array
    {
        return array_merge(self::usuario($user), [
            'permisos' => self::permisosDe($user),
        ]);
    }

    public static function rolResumen(?string $clave): ?array
    {
        if (! $clave) {
            return null;
        }

        $definicion = self::ROLES[$clave] ?? null;
        $modelo = Role::query()->where('clave', $clave)->first();

        return [
            'id' => self::uuid($modelo?->id ?? (array_search($clave, array_keys(self::ROLES), true) + 1)),
            'codigo' => $definicion['codigo'] ?? strtoupper($clave),
            'nombre' => $definicion['nombre'] ?? ($modelo?->nombre ?? $clave),
        ];
    }

    public static function rolCompleto(Role $role): array
    {
        $definicion = self::ROLES[$role->clave] ?? null;

        return [
            'id' => self::uuid($role->id),
            'codigo' => $definicion['codigo'] ?? strtoupper($role->clave),
            'nombre' => $role->nombre,
            'descripcion' => $role->descripcion,
            'sistema' => $definicion !== null,
            'permisos' => $definicion['permisos'] ?? [],
        ];
    }

    public static function catalogoPermisos(): array
    {
        $vistos = [];

        foreach (self::ROLES as $rol) {
            foreach ($rol['permisos'] as $codigo) {
                $vistos[$codigo] = [
                    'codigo' => $codigo,
                    'modulo' => explode('.', $codigo)[0],
                    'descripcion' => str_replace(['.', '_'], [' ', ' '], $codigo),
                ];
            }
        }

        return array_values($vistos);
    }

    public static function categoria(Categoria $categoria): array
    {
        return [
            'id' => self::uuid($categoria->id),
            'codigo' => $categoria->slug,
            'nombre' => $categoria->nombre,
            'descripcion' => $categoria->descripcion,
            'activa' => true,
            'ruta' => [$categoria->slug],
            'padreId' => null,
        ];
    }

    public static function estadoExpediente(string $estado): string
    {
        return match ($estado) {
            'abierto', 'en_proceso' => 'en_tramite',
            'en_revision' => 'en_revision',
            'cerrado' => 'cerrado',
            'archivado' => 'archivado',
            'borrador' => 'borrador',
            default => in_array($estado, ['borrador', 'en_tramite', 'en_revision', 'cerrado', 'archivado'], true)
                ? $estado
                : 'en_tramite',
        };
    }

    public static function estadoInternoExpediente(string $accionOEstado): string
    {
        return match ($accionOEstado) {
            'abrir', 'reabrir', 'en_tramite' => 'abierto',
            'enviar_a_revision', 'en_revision' => 'en_revision',
            'devolver', 'borrador' => 'borrador',
            'cerrar', 'cerrado' => 'cerrado',
            'archivar', 'archivado' => 'archivado',
            default => $accionOEstado,
        };
    }

    public static function expediente(Expediente $expediente): array
    {
        $expediente->loadMissing('documentos');

        return [
            'id' => self::uuid($expediente->id),
            'codigo' => $expediente->codigo,
            'titulo' => $expediente->titulo,
            'descripcion' => $expediente->descripcion,
            'estado' => self::estadoExpediente($expediente->estado),
            'nivelConfidencialidad' => 'interno',
            'categoriaId' => $expediente->categoria_id ? self::uuid($expediente->categoria_id) : null,
            'responsableId' => $expediente->responsable_id ? self::uuid($expediente->responsable_id) : self::uuid(0),
            'etiquetas' => array_values(array_filter([$expediente->prioridad])),
            'documentos' => $expediente->documentos->count(),
            'creadoPor' => $expediente->creado_por ? self::uuid($expediente->creado_por) : null,
            'creadoEn' => optional($expediente->created_at)?->toIso8601String(),
            'actualizadoEn' => optional($expediente->updated_at)?->toIso8601String(),
            'cerradoEn' => $expediente->fecha_cierre?->toIso8601String(),
        ];
    }

    public static function estadoProcesamiento(Documento $documento): string
    {
        if ($documento->embedding_estado === 'indexado') {
            return 'indexado';
        }

        if ($documento->ocr_estado === 'error' || $documento->embedding_estado === 'error') {
            return 'error';
        }

        if (in_array($documento->ocr_estado, ['pendiente', 'procesando'], true) && ! $documento->contenido_ocr) {
            return 'en_ocr';
        }

        if ($documento->ocr_estado === 'procesado' && $documento->embedding_estado !== 'indexado') {
            return 'en_indexacion';
        }

        return 'pendiente_carga';
    }

    public static function tipoDocumento(string $tipo): string
    {
        $mapa = [
            'reglamento' => 'reglamento',
            'resolucion' => 'resolucion',
            'informe' => 'informe',
            'acta' => 'acta',
            'contrato' => 'contrato',
            'oficio' => 'oficio',
        ];

        return $mapa[strtolower($tipo)] ?? 'otro';
    }

    public static function documento(Documento $documento): array
    {
        $version = $documento->versiones()->first();

        return [
            'id' => self::uuid($documento->id),
            'expedienteId' => self::uuid($documento->expediente_id),
            'titulo' => $documento->titulo,
            'descripcion' => $documento->descripcion,
            'tipo' => self::tipoDocumento($documento->tipo),
            'origen' => 'digital',
            'categoriaId' => $documento->categoria_id ? self::uuid($documento->categoria_id) : null,
            'nivelConfidencialidad' => 'interno',
            'estadoProcesamiento' => self::estadoProcesamiento($documento),
            'versionVigente' => $version ? [
                'id' => self::uuid($version->id),
                'numero' => $version->numero,
                'esVigente' => true,
                'hashSha256' => null,
            ] : null,
            'creadoPor' => $documento->subido_por ? self::uuid($documento->subido_por) : null,
            'creadoEn' => optional($documento->created_at)?->toIso8601String(),
            'actualizadoEn' => optional($documento->updated_at)?->toIso8601String(),
        ];
    }

    public static function version(DocumentoVersion $version, Documento $documento): array
    {
        $vigente = (int) $documento->version_actual === (int) $version->numero;

        return [
            'id' => self::uuid($version->id),
            'documentoId' => self::uuid($documento->id),
            'numero' => $version->numero,
            'nombreArchivo' => $documento->archivo_nombre,
            'tipoMime' => $documento->mime ?: 'application/pdf',
            'tamanoBytes' => $documento->tamano,
            'hashSha256' => null,
            'notas' => $version->resumen_cambios,
            'estado' => $vigente ? 'vigente' : 'sustituida',
            'esVigente' => $vigente,
            'estadoOcr' => $documento->ocr_estado === 'procesado' ? 'completado' : 'en_cola',
            'estadoIndexacion' => $documento->embedding_estado === 'indexado' ? 'completado' : 'en_cola',
            'restauradaDesdeVersionId' => null,
            'subidoPor' => $version->subido_por ? self::uuid($version->subido_por) : null,
            'creadoEn' => optional($version->created_at)?->toIso8601String(),
            'confirmadoEn' => optional($version->created_at)?->toIso8601String(),
        ];
    }

    public static function notificacion(Notificacion $notificacion): array
    {
        $tipo = match ($notificacion->tipo) {
            'documento', 'ocr' => 'documento_indexado',
            'expediente' => 'expediente_asignado',
            default => 'resumen_listo',
        };

        return [
            'id' => self::uuid($notificacion->id),
            'tipo' => $tipo,
            'titulo' => $notificacion->titulo,
            'mensaje' => $notificacion->mensaje,
            'leida' => (bool) $notificacion->leida,
            'recursoTipo' => str_contains((string) $notificacion->enlace, 'documento') ? 'documento' : 'expediente',
            'recursoId' => null,
            'creadoEn' => optional($notificacion->created_at)?->toIso8601String(),
            'leidaEn' => $notificacion->leida ? optional($notificacion->updated_at)?->toIso8601String() : null,
        ];
    }

    public static function evento(Auditoria $evento): array
    {
        $recurso = match ($evento->modulo) {
            'usuarios' => 'usuario',
            'expedientes' => 'expediente',
            'documentos', 'ocr' => 'documento',
            'asistente' => 'conversacion',
            'busqueda' => 'busqueda',
            default => 'expediente',
        };

        return [
            'id' => self::uuid($evento->id),
            'actorId' => $evento->user_id ? self::uuid($evento->user_id) : self::uuid(0),
            'actorCorreo' => $evento->user?->email,
            'accion' => $evento->accion,
            'recursoTipo' => $recurso,
            'recursoId' => null,
            'resultado' => 'exito',
            'direccionIp' => $evento->ip,
            'requestId' => null,
            'metadatos' => ['descripcion' => $evento->descripcion, 'modulo' => $evento->modulo],
            'ocurridoEn' => optional($evento->created_at)?->toIso8601String(),
        ];
    }

    public static function conversacion(ConsultaRag $consulta): array
    {
        return [
            'id' => self::uuid($consulta->id),
            'titulo' => mb_strimwidth($consulta->pregunta, 0, 80, '…'),
            'alcance' => (object) [],
            'creadoEn' => optional($consulta->created_at)?->toIso8601String(),
            'actualizadoEn' => optional($consulta->updated_at)?->toIso8601String(),
        ];
    }

    public static function mensaje(ConsultaRag $consulta): array
    {
        $fuentes = collect($consulta->fuentes ?? [])->map(function (array $fuente, int $indice) {
            return [
                'orden' => $indice + 1,
                'documentoId' => isset($fuente['documento_id']) ? self::uuid((int) $fuente['documento_id']) : null,
                'titulo' => $fuente['titulo'] ?? $fuente['codigo'] ?? 'Fuente',
                'fragmento' => $fuente['texto'] ?? '',
                'puntaje' => $fuente['puntaje'] ?? null,
            ];
        })->all();

        return [
            'id' => self::uuid($consulta->id),
            'pregunta' => $consulta->pregunta,
            'respuesta' => $consulta->respuesta,
            'respuestaSustentada' => $fuentes !== [],
            'fuentes' => $fuentes,
            'modelo' => $consulta->modelo_simulado,
            'creadoEn' => optional($consulta->created_at)?->toIso8601String(),
        ];
    }
}
