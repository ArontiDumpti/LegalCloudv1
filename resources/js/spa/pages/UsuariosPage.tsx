import { useEffect, useMemo, useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar } from '../ui';
import type { Paginacion, Perfil, RolResumen, Usuario } from '../types';

type Rol = RolResumen & { descripcion?: string; sistema?: boolean; permisos?: string[] };

const POR_PAGINA = 10;

export default function UsuariosPage({ onError, perfil }: { onError: (m: string) => void; perfil: Perfil }) {
    const [usuarios, setUsuarios] = useState<Usuario[]>([]);
    const [roles, setRoles] = useState<Rol[]>([]);
    const [correo, setCorreo] = useState('');
    const [nombre, setNombre] = useState('');
    const [rol, setRol] = useState('CONSULTOR');
    const [busqueda, setBusqueda] = useState('');
    const [filtroRol, setFiltroRol] = useState('');
    const [filtroEstado, setFiltroEstado] = useState('');
    const [pagina, setPagina] = useState(1);
    const [total, setTotal] = useState(0);
    const [paginas, setPaginas] = useState(1);
    const [cargando, setCargando] = useState(true);

    const consulta = useMemo(() => {
        const params = new URLSearchParams({
            pagina: String(pagina),
            limite: String(POR_PAGINA),
        });
        if (busqueda.trim()) {
            params.set('q', busqueda.trim());
        }
        if (filtroRol) {
            params.set('rol', filtroRol);
        }
        if (filtroEstado) {
            params.set('estado', filtroEstado);
        }

        return params.toString();
    }, [busqueda, filtroRol, filtroEstado, pagina]);

    async function cargarRoles() {
        const r = await api<Paginacion<Rol>>('/roles');
        setRoles(r.datos);
    }

    async function cargarUsuarios() {
        setCargando(true);
        try {
            const data = await api<Paginacion<Usuario>>(`/usuarios?${consulta}`);
            setUsuarios(data.datos);
            setTotal(data.paginacion.total ?? data.datos.length);
            setPaginas(data.paginacion.paginas ?? 1);
        } finally {
            setCargando(false);
        }
    }

    useEffect(() => {
        void cargarRoles().catch((e) => capturar(e, onError));
    }, []);

    useEffect(() => {
        const espera = window.setTimeout(() => {
            void cargarUsuarios().catch((e) => capturar(e, onError));
        }, busqueda ? 250 : 0);

        return () => window.clearTimeout(espera);
    }, [consulta]);

    async function invitar(evento: FormEvent) {
        evento.preventDefault();
        try {
            await api('/usuarios', {
                method: 'POST',
                body: JSON.stringify({ correo, nombre, roles: [rol] }),
            });
            setCorreo('');
            setNombre('');
            setPagina(1);
            await cargarUsuarios();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function cambiarRol(usuarioId: string, codigo: string) {
        try {
            await api(`/usuarios/${usuarioId}/roles`, { method: 'PUT', body: JSON.stringify({ roles: [codigo] }) });
            await cargarUsuarios();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function alternar(usuario: Usuario) {
        try {
            const accion = usuario.estado === 'activo' ? 'suspender' : 'reactivar';
            await api(`/usuarios/${usuario.id}/${accion}`, { method: 'POST' });
            await cargarUsuarios();
        } catch (e) {
            capturar(e, onError);
        }
    }

    const desde = total === 0 ? 0 : (pagina - 1) * POR_PAGINA + 1;
    const hasta = Math.min(pagina * POR_PAGINA, total);

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Usuarios y roles</h1>
                <p className="text-sm text-slate-500">Invite, cambie el rol o suspenda cuentas. {total} usuario{total === 1 ? '' : 's'} en total.</p>
            </div>

            {perfil.permisos.includes('usuarios.invitar') && (
                <form onSubmit={(e) => void invitar(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5 md:grid-cols-4">
                    <input className="h-10 rounded-lg border px-3" placeholder="Nombre" value={nombre} onChange={(e) => setNombre(e.target.value)} required />
                    <input className="h-10 rounded-lg border px-3" placeholder="Correo" value={correo} onChange={(e) => setCorreo(e.target.value)} required />
                    <select className="h-10 rounded-lg border px-3" value={rol} onChange={(e) => setRol(e.target.value)}>
                        {roles.map((r) => (
                            <option key={r.id} value={r.codigo}>
                                {r.nombre}
                            </option>
                        ))}
                    </select>
                    <button className="rounded-full bg-royal px-4 py-2 text-sm font-semibold text-white">Invitar</button>
                </form>
            )}

            <section className="rounded-2xl border border-mist bg-white">
                <div className="grid gap-3 border-b border-mist p-4 md:grid-cols-[1fr_180px_160px]">
                    <input
                        className="h-10 rounded-lg border px-3"
                        placeholder="Buscar por nombre o correo"
                        value={busqueda}
                        onChange={(e) => {
                            setBusqueda(e.target.value);
                            setPagina(1);
                        }}
                    />
                    <select
                        className="h-10 rounded-lg border px-3"
                        value={filtroRol}
                        onChange={(e) => {
                            setFiltroRol(e.target.value);
                            setPagina(1);
                        }}
                    >
                        <option value="">Todos los roles</option>
                        {roles.map((r) => (
                            <option key={r.id} value={r.codigo}>
                                {r.nombre}
                            </option>
                        ))}
                    </select>
                    <select
                        className="h-10 rounded-lg border px-3"
                        value={filtroEstado}
                        onChange={(e) => {
                            setFiltroEstado(e.target.value);
                            setPagina(1);
                        }}
                    >
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="suspendido">Suspendidos</option>
                    </select>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full text-left text-sm">
                        <thead className="bg-ice text-xs tracking-wide text-slate-500 uppercase">
                            <tr>
                                <th className="px-4 py-3 font-semibold">Usuario</th>
                                <th className="px-4 py-3 font-semibold">Rol</th>
                                <th className="px-4 py-3 font-semibold">Estado</th>
                                <th className="px-4 py-3 font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {usuarios.map((u) => (
                                <tr key={u.id} className="border-t border-mist">
                                    <td className="px-4 py-3">
                                        <p className="font-semibold">
                                            {u.nombre} {u.apellido}
                                        </p>
                                        <p className="text-xs text-slate-500">{u.correo}</p>
                                    </td>
                                    <td className="px-4 py-3">
                                        {perfil.permisos.includes('usuarios.actualizar') ? (
                                            <select
                                                className="h-9 max-w-56 rounded-lg border px-2 text-sm"
                                                value={u.roles[0]?.codigo}
                                                onChange={(e) => void cambiarRol(u.id, e.target.value)}
                                            >
                                                {roles.map((r) => (
                                                    <option key={r.id} value={r.codigo}>
                                                        {r.nombre}
                                                    </option>
                                                ))}
                                            </select>
                                        ) : (
                                            <span>{u.roles[0]?.nombre}</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">{badge(u.estado)}</td>
                                    <td className="px-4 py-3">
                                        {perfil.permisos.includes('usuarios.suspender') && (
                                            <button type="button" className="text-sm text-royal" onClick={() => void alternar(u)}>
                                                {u.estado === 'activo' ? 'Suspender' : 'Reactivar'}
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {!cargando && usuarios.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-8 text-center text-sm text-slate-500">
                                        No hay usuarios con esos filtros.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-3 border-t border-mist px-4 py-3 text-sm text-slate-600">
                    <p>
                        {cargando ? 'Cargando…' : `Mostrando ${desde}–${hasta} de ${total}`}
                    </p>
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            className="rounded-lg border px-3 py-1.5 disabled:opacity-40"
                            disabled={pagina <= 1}
                            onClick={() => setPagina((p) => Math.max(1, p - 1))}
                        >
                            Anterior
                        </button>
                        <span>
                            {pagina} / {paginas}
                        </span>
                        <button
                            type="button"
                            className="rounded-lg border px-3 py-1.5 disabled:opacity-40"
                            disabled={pagina >= paginas}
                            onClick={() => setPagina((p) => p + 1)}
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </section>

            <article className="rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-display text-xl font-bold">Roles</h2>
                <ul className="mt-3 grid gap-2 sm:grid-cols-2">
                    {roles.map((r) => (
                        <li key={r.id} className="rounded-xl bg-ice px-4 py-3 text-sm">
                            <span className="font-semibold">{r.nombre}</span>
                            {r.descripcion ? <p className="mt-1 text-xs text-slate-500">{r.descripcion}</p> : null}
                        </li>
                    ))}
                </ul>
            </article>
        </div>
    );
}
