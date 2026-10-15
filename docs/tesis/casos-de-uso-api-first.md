# Casos de uso de contexto — LegalCloud AI

Figuras listas para insertar en la tesis:

- `docs/tesis/figuras/contexto-legalcloud-ai.svg` — diagrama de contexto
- `docs/tesis/figuras/casos-de-uso-legalcloud-ai.svg` — casos de uso de contexto
- `docs/tesis/figuras/inteligencia-documental.svg` — inclusiones, extensiones y sistemas externos
- `docs/tesis/figuras/secuencia-carga-pregunta.svg` — secuencia de carga, procesamiento y pregunta sustentada

La generalización de actores se lee en el diagrama de casos de uso: el actor especializado también ejecuta los casos del actor general. Las relaciones `«include»` y `«extend»` están en el tercero, para que el diagrama general quepa en una página. Los sistemas externos aparecen a la derecha, fuera de la frontera de la API.

El nivel es el del sistema. Cada caso agrupa operaciones del contrato `openapi/legalcloud-ai.v1.yaml`. La frontera es la API v1: almacenamiento, OCR, índice de embeddings, modelo de lenguaje y correo quedan fuera, como actores secundarios.

## Frontera y actores

Actores primarios, de menor a mayor capacidad. La flecha de generalización significa que el actor especializado también ejecuta los casos del actor general.

| Actor | Responsabilidad en el sistema | Alcance |
|---|---|---|
| Usuario consultor | Consulta, descarga, búsqueda y preguntas | Expedientes en los que es participante |
| Especialista | Clasificación, procesamiento, texto extraído y resúmenes | Expedientes asignados |
| Responsable documental | Ciclo de vida del fondo documental | Organización completa |
| Administrador | Identidad, roles y auditoría | Organización completa |

Actores secundarios: almacenamiento de objetos, motor OCR, índice semántico, modelo de lenguaje y correo electrónico.

El consultor y el especialista pasan además por la ACL del expediente (`lector`, `revisor`, `editor`, `responsable`). Un intento fuera de ese alcance termina en `403` y en auditoría.

## Catálogo

| Caso | Actor que lo inicia | Qué logra | Operaciones de la API |
|---|---|---|---|
| UC-01 Autenticarse | Usuario consultor | Abre, renueva o cierra la sesión, acepta la invitación y recupera el acceso | `POST /auth/iniciar-sesion`, `/auth/renovar`, `/auth/cerrar-sesion`, `/auth/aceptar-invitacion`, `/auth/recuperar-contrasena`, `/auth/cambiar-contrasena`, `GET /auth/yo` |
| UC-02 Consultar notificaciones | Usuario consultor | Revisa y marca avisos de asignación, procesamiento y resumen | `GET /notificaciones`, `GET /notificaciones/no-leidas/conteo`, `POST /notificaciones/{id}/lectura`, `POST /notificaciones/lectura` |
| UC-03 Consultar tablero | Usuario consultor | Ve indicadores de su alcance | `GET /dashboard` |
| UC-04 Consultar expedientes y documentos | Usuario consultor | Navega el fondo autorizado, incluidas las versiones | `GET /expedientes`, `GET /expedientes/{id}`, `GET /expedientes/{id}/documentos`, `GET /documentos/{id}`, `GET /documentos/{id}/versiones`, `GET /categorias` |
| UC-05 Descargar versión | Usuario consultor | Obtiene el binario por una URL temporal | `GET /documentos/{id}/versiones/{versionId}/descarga` |
| UC-06 Buscar en el fondo documental | Usuario consultor | Recupera fragmentos por modo léxico, semántico o híbrido | `POST /busquedas`, `GET /busquedas`, `GET /busquedas/{id}` |
| UC-07 Preguntar al asistente | Usuario consultor | Formula una pregunta en lenguaje natural y recibe respuesta con fuentes | `POST /conversaciones`, `GET /conversaciones`, `GET /conversaciones/{id}`, `POST /conversaciones/{id}/mensajes` |
| UC-08 Clasificar documento | Especialista | Corrige tipo, categoría, confidencialidad y metadatos | `PATCH /documentos/{id}`, `PATCH /expedientes/{id}` |
| UC-09 Procesar documento | Especialista | Confirma el archivo y ejecuta indexación; puede reprocesar | `POST .../versiones/{versionId}/confirmar`, `POST .../procesamiento`, `GET /trabajos/{id}` |
| UC-10 Generar resumen sustentado | Especialista | Pide un resumen de expediente, documento o versión | `POST /resumenes`, `GET /resumenes/{id}` |
| UC-11 Consultar texto extraído | Especialista | Lee el texto por página y su confianza de OCR | `GET .../versiones/{versionId}/texto` |
| UC-12 Registrar expediente | Responsable documental | Abre un expediente en borrador | `POST /expedientes` |
| UC-13 Transicionar estado | Responsable documental | Avanza o devuelve el ciclo de vida | `POST /expedientes/{id}/transiciones` |
| UC-14 Asignar participantes | Responsable documental | Define la ACL del expediente | `GET` y `PUT /expedientes/{id}/participantes` |
| UC-15 Cargar y versionar documento | Responsable documental | Reserva la carga, publica una versión o restaura una anterior como versión nueva | `POST /expedientes/{id}/documentos`, `POST /documentos/{id}/versiones`, `POST .../confirmar`, `POST .../restaurar` |
| UC-16 Archivar documento | Responsable documental | Retira el documento del índice sin borrar su historia | `POST /documentos/{id}/archivar` |
| UC-17 Administrar categorías | Responsable documental | Mantiene la taxonomía | `POST /categorias`, `PATCH /categorias/{id}`, `POST /categorias/{id}/desactivar` |
| UC-18 Consultar trazabilidad | Responsable documental | Revisa operaciones y consultas del expediente | `GET /expedientes/{id}/trazabilidad` |
| UC-19 Gestionar usuarios | Administrador | Invita, actualiza, suspende y reactiva cuentas | `GET` y `POST /usuarios`, `PATCH /usuarios/{id}`, `POST /usuarios/{id}/suspender`, `POST /usuarios/{id}/reactivar` |
| UC-20 Gestionar roles y permisos | Administrador | Administra el RBAC y los roles de cada usuario | `GET /permisos`, `GET` y `POST /roles`, `PATCH /roles/{id}`, `PUT /roles/{id}/permisos`, `PUT /usuarios/{id}/roles` |
| UC-21 Consultar auditoría | Administrador | Consulta la bitácora de la organización | `GET /auditoria`, `GET /auditoria/{id}` |
| UC-22 Registrar evidencia de la consulta | Inclusión de UC-06, UC-07 y UC-10 | Conserva fragmento, página, versión y hash de lo usado para responder | Queda en el cuerpo de búsqueda, mensaje y resumen, y en auditoría |
| UC-23 Extraer texto por OCR | Extensión de UC-09 | Obtiene texto cuando el origen es digitalizado o el archivo no trae capa de texto | Etapa `ocr` del trabajo de procesamiento |

## Relaciones

- **Include.** Cargar y versionar incluye procesar el documento: al confirmar la versión, la API encola el trabajo. Buscar, preguntar y resumir incluyen registrar la evidencia.
- **Extend.** Extraer texto por OCR extiende el procesamiento solo si el documento es digitalizado o no contiene texto utilizable.
- **Generalización.** Administrador → responsable documental → especialista → usuario consultor.

## Caso ilustrativo

El consultor pregunta: «¿Cuáles son los requisitos establecidos para realizar el procedimiento de licencia de funcionamiento?».

1. UC-01, si aún no tiene sesión.
2. UC-07 sobre una conversación acotada al expediente.
3. UC-22 guarda reglamento y resolución citados, con página y hash.
4. Si la evidencia no alcanza, la respuesta llega con `respuestaSustentada = false`.

Ese intercambio queda disponible para UC-18 y UC-21.

## Lectura de las figuras

En el diagrama de contexto, las personas hablan solo con LegalCloud AI. La API orquesta a los cinco sistemas externos.

En el diagrama de casos de uso, los óvalos son capacidades del contrato, no pantallas ni tablas. Cada actor se une con un trazo al borde superior de los casos que inicia. La flecha discontinua señala el caso incluido o el caso base. La flecha hueca apunta al actor general. A la derecha, cada sistema externo recibe la línea de los casos que lo usan. `GET /salud` no aparece: es supervisión de plataforma, no un caso de un actor de negocio.

En la figura de inteligencia documental, cargar y versionar incluye procesar el documento; el OCR extiende ese procesamiento cuando el archivo es digitalizado o no trae texto; buscar, preguntar y resumir incluyen el registro de la evidencia.

En el diagrama de secuencia, el responsable no envía el binario a la API: reserva la carga, hace el PUT contra el almacenamiento y confirma con el hash. La API responde `202` y, ya en segundo plano, llama al OCR solo si el archivo es digitalizado o no trae texto; después indexa. El consultor pregunta dentro de una conversación con alcance. Si hay fragmentos vigentes y autorizados, el modelo responde solo con ellos y `respuestaSustentada` es verdadero; si no alcanzan, la API no llama al modelo y lo declara. Pregunta, fragmentos y respuesta quedan auditados.
