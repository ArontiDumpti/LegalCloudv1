import { Bell, FileSearch, FileText, FolderOpen, LayoutDashboard, LogOut, MessageSquareText, ShieldCheck, Tags, UserRound, Users } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { api, guardarTokens, leerTokens } from './api';
import AsistentePage from './pages/AsistentePage';
import AuditoriaPage from './pages/AuditoriaPage';
import BusquedaPage from './pages/BusquedaPage';
import CategoriasPage from './pages/CategoriasPage';
import DashboardPage from './pages/DashboardPage';
import DocumentoDetallePage from './pages/DocumentoDetallePage';
import DocumentosPage from './pages/DocumentosPage';
import ExpedienteDetallePage from './pages/ExpedienteDetallePage';
import ExpedientesPage from './pages/ExpedientesPage';
import LoginPage from './pages/LoginPage';
import NotificacionesPage from './pages/NotificacionesPage';
import PerfilPage from './pages/PerfilPage';
import UsuariosPage from './pages/UsuariosPage';
import type { Perfil } from './types';
import { ir } from './ui';

function rutaActual(): string {
    const raw = window.location.pathname.replace(/^\/app\/?/, '/');
    return raw === '' ? '/' : raw;
}

function puede(perfil: Perfil | null, permiso: string): boolean {
    return Boolean(perfil?.permisos.includes(permiso));
}

export default function App() {
    const [path, setPath] = useState(rutaActual);
    const [perfil, setPerfil] = useState<Perfil | null>(null);
    const [cargando, setCargando] = useState(Boolean(leerTokens()));
    const [alerta, setAlerta] = useState<string | null>(null);

    useEffect(() => {
        const sync = () => setPath(rutaActual());
        window.addEventListener('popstate', sync);
        window.addEventListener('spa:nav', sync);
        return () => {
            window.removeEventListener('popstate', sync);
            window.removeEventListener('spa:nav', sync);
        };
    }, []);

    useEffect(() => {
        if (!leerTokens()) {
            setCargando(false);
            return;
        }

        api<Perfil>('/auth/yo')
            .then((yo) => setPerfil(yo))
            .catch(() => {
                guardarTokens(null);
                setPerfil(null);
            })
            .finally(() => setCargando(false));
    }, []);

    const nav = useMemo(() => {
        const items = [
            { href: '/dashboard', label: 'Tablero', icon: LayoutDashboard, show: true },
            { href: '/expedientes', label: 'Expedientes', icon: FolderOpen, show: puede(perfil, 'expedientes.leer') },
            { href: '/documentos', label: 'Documentos', icon: FileText, show: puede(perfil, 'documentos.leer') },
            { href: '/categorias', label: 'Categorías', icon: Tags, show: puede(perfil, 'categorias.leer') },
            { href: '/busqueda', label: 'Búsqueda', icon: FileSearch, show: puede(perfil, 'busqueda.consultar') },
            { href: '/asistente', label: 'Asistente', icon: MessageSquareText, show: puede(perfil, 'asistente.conversar') },
            { href: '/notificaciones', label: 'Avisos', icon: Bell, show: puede(perfil, 'notificaciones.leer') },
            { href: '/usuarios', label: 'Usuarios y roles', icon: Users, show: puede(perfil, 'usuarios.leer') },
            { href: '/auditoria', label: 'Auditoría', icon: ShieldCheck, show: puede(perfil, 'auditoria.leer') },
            { href: '/perfil', label: 'Mi perfil', icon: UserRound, show: true },
        ];

        return items.filter((item) => item.show);
    }, [perfil]);

    const publico = path === '/' || path === '/login';

    if (cargando) {
        return <div className="flex min-h-screen items-center justify-center text-sm text-slate-500">Cargando LegalCloud…</div>;
    }

    if (!perfil && !publico && path !== '/login') {
        queueMicrotask(() => ir('/login'));
    }

    if (!perfil) {
        return (
            <LoginPage
                onSesion={async (token) => {
                    guardarTokens(token);
                    const yo = await api<Perfil>('/auth/yo');
                    setPerfil(yo);
                    ir('/dashboard');
                }}
            />
        );
    }

    const partes = path.split('/').filter(Boolean);
    const seccion = partes[0] || 'dashboard';
    const id = partes[1];

    async function salir() {
        try {
            await api('/auth/cerrar-sesion', { method: 'POST' });
        } catch {
            // ignore
        }
        guardarTokens(null);
        setPerfil(null);
        ir('/login');
    }

    return (
        <div className="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
            <aside className="border-b border-mist bg-white lg:border-r lg:border-b-0">
                <div className="flex items-center gap-2 px-5 py-5">
                    <span className="flex size-9 items-center justify-center rounded-lg bg-navy text-sm text-white">LC</span>
                    <div>
                        <p className="font-semibold">
                            LegalCloud <span className="text-royal">AI</span>
                        </p>
                        <p className="text-[11px] tracking-wide text-royal uppercase">Frontend API-first</p>
                    </div>
                </div>
                <nav className="grid gap-1 px-3 pb-4">
                    {nav.map((item) => {
                        const activo = path === item.href || path.startsWith(`${item.href}/`);
                        return (
                            <button
                                key={item.href}
                                type="button"
                                onClick={() => ir(item.href)}
                                className={`flex items-center gap-2 rounded-xl px-3 py-2 text-left text-sm ${activo ? 'bg-ice font-semibold text-royal' : 'text-slate-600 hover:bg-ice'}`}
                            >
                                <item.icon className="size-4" />
                                {item.label}
                            </button>
                        );
                    })}
                </nav>
                <div className="border-t border-mist px-5 py-4 text-xs text-slate-500">
                    <p className="font-semibold text-navy">
                        {perfil.nombre} {perfil.apellido}
                    </p>
                    <p>{perfil.roles[0]?.nombre}</p>
                    <button type="button" onClick={() => void salir()} className="mt-3 inline-flex items-center gap-1 text-royal">
                        <LogOut className="size-3.5" /> Cerrar sesión
                    </button>
                </div>
            </aside>
            <main className="min-w-0 p-4 md:p-8">
                {alerta && (
                    <p className="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
                        {alerta}
                        <button type="button" className="ml-2 underline" onClick={() => setAlerta(null)}>
                            Cerrar
                        </button>
                    </p>
                )}
                {seccion === 'dashboard' && <DashboardPage perfil={perfil} />}
                {seccion === 'expedientes' && !id && <ExpedientesPage onError={setAlerta} puedeCrear={puede(perfil, 'expedientes.crear')} />}
                {seccion === 'expedientes' && id && <ExpedienteDetallePage id={id} onError={setAlerta} perfil={perfil} />}
                {seccion === 'documentos' && !id && <DocumentosPage onError={setAlerta} />}
                {seccion === 'documentos' && id && <DocumentoDetallePage id={id} onError={setAlerta} perfil={perfil} />}
                {seccion === 'categorias' && <CategoriasPage onError={setAlerta} puedeCrear={puede(perfil, 'categorias.crear')} />}
                {seccion === 'busqueda' && <BusquedaPage onError={setAlerta} />}
                {seccion === 'asistente' && <AsistentePage onError={setAlerta} />}
                {seccion === 'notificaciones' && <NotificacionesPage onError={setAlerta} />}
                {seccion === 'usuarios' && <UsuariosPage onError={setAlerta} perfil={perfil} />}
                {seccion === 'auditoria' && <AuditoriaPage onError={setAlerta} />}
                {seccion === 'perfil' && <PerfilPage perfil={perfil} onError={setAlerta} />}
            </main>
        </div>
    );
}
