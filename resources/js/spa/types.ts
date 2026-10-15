export type Token = {
    tokenType: 'Bearer';
    accessToken: string;
    refreshToken: string;
    expiraEn: number;
};

export type RolResumen = {
    id: string;
    codigo: string;
    nombre: string;
};

export type Perfil = {
    id: string;
    correo: string;
    nombre: string;
    apellido?: string | null;
    estado: string;
    roles: RolResumen[];
    permisos: string[];
};

export type Paginacion<T> = {
    datos: T[];
    paginacion: { limit: number; nextCursor: string | null };
};

export type Expediente = {
    id: string;
    codigo: string;
    titulo: string;
    descripcion?: string | null;
    estado: string;
    nivelConfidencialidad: string;
    categoriaId?: string | null;
    responsableId: string;
    etiquetas?: string[];
    documentos: number;
    creadoEn: string;
};

export type Documento = {
    id: string;
    expedienteId: string;
    titulo: string;
    descripcion?: string | null;
    tipo: string;
    origen: string;
    categoriaId?: string | null;
    estadoProcesamiento: string;
    versionVigente?: { id: string; numero: number; esVigente: boolean } | null;
    creadoEn: string;
};

export type Categoria = {
    id: string;
    codigo: string;
    nombre: string;
    descripcion?: string | null;
    activa: boolean;
};

export type Usuario = {
    id: string;
    correo: string;
    nombre: string;
    apellido?: string | null;
    estado: string;
    roles: RolResumen[];
};

export type Notificacion = {
    id: string;
    tipo: string;
    titulo: string;
    mensaje: string;
    leida: boolean;
    creadoEn: string;
};

export type EventoAuditoria = {
    id: string;
    actorId: string;
    actorCorreo?: string;
    accion: string;
    recursoTipo: string;
    resultado: string;
    metadatos?: { descripcion?: string; modulo?: string };
    ocurridoEn: string;
};

export type Dashboard = {
    expedientesPorEstado: Record<string, number>;
    documentosPorProcesamiento: Record<string, number>;
    consultas7d?: number;
    resumenes7d?: number;
    almacenamientoBytes?: number;
    actividadReciente?: EventoAuditoria[];
    generadoEn: string;
};

export type ResultadoBusqueda = {
    id: string;
    consulta: string;
    modo: string;
    resultados: Array<{ documento: Documento; puntaje: number; fragmento: string }>;
    creadoEn: string;
};

export type Mensaje = {
    id: string;
    pregunta: string;
    respuesta: string;
    respuestaSustentada: boolean;
    fuentes: Array<{ titulo: string; fragmento: string; puntaje?: number }>;
    creadoEn: string;
};

export type Conversacion = {
    id: string;
    titulo: string;
    mensajes?: Mensaje[];
    creadoEn: string;
};
