import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
    role_label?: string | null;
    puede_usuarios_auditoria?: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    auth: Auth;
    flash?: { success?: string | null };
    notificaciones_count?: number;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role?: string;
    cargo?: string | null;
    activo?: boolean;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Categoria {
    id: number;
    nombre: string;
    slug: string;
    descripcion?: string | null;
    color: string;
    expedientes_count?: number;
    documentos_count?: number;
}

export interface Expediente {
    id: number;
    codigo: string;
    titulo: string;
    descripcion?: string | null;
    estado: string;
    prioridad: string;
    fecha_apertura?: string | null;
    categoria?: Categoria | null;
    responsable?: User | null;
    creador?: User | null;
    documentos?: Documento[];
    documentos_count?: number;
}

export interface DocumentoVersion {
    id: number;
    numero: number;
    resumen_cambios?: string | null;
    contenido?: string | null;
    created_at: string;
    autor?: User | null;
}

export interface Documento {
    id: number;
    codigo: string;
    titulo: string;
    tipo: string;
    descripcion?: string | null;
    archivo_nombre?: string | null;
    archivo_ruta?: string | null;
    archivo_url?: string | null;
    mime?: string | null;
    version_actual: number;
    ocr_estado: string;
    contenido_ocr?: string | null;
    embedding_estado: string;
    created_at: string;
    expediente?: Expediente | null;
    categoria?: Categoria | null;
    autor?: User | null;
    versiones?: DocumentoVersion[];
}

export interface FuenteRag {
    documento_id: number;
    codigo: string;
    titulo: string;
    expediente?: string | null;
    texto: string;
    puntaje: number;
}

export interface ConsultaRag {
    id: number;
    pregunta: string;
    respuesta: string;
    fuentes?: FuenteRag[] | null;
    modelo_simulado: string;
    created_at: string;
    user?: User | null;
}

export interface NotificacionItem {
    id: number;
    titulo: string;
    mensaje: string;
    tipo: string;
    leida: boolean;
    enlace?: string | null;
    created_at: string;
}

export interface AuditoriaEvento {
    id: number;
    accion: string;
    modulo: string;
    descripcion?: string | null;
    ip?: string | null;
    created_at: string;
    user?: User | null;
}

export interface RoleItem {
    id: number;
    clave: string;
    nombre: string;
    descripcion?: string | null;
}
