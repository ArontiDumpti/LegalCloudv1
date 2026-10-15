import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar } from '../ui';
import type { Paginacion, Perfil, RolResumen, Usuario } from '../types';

type Rol = RolResumen & { descripcion?: string; sistema?: boolean; permisos?: string[] };

export default function UsuariosPage({ onError, perfil }: { onError: (m: string) => void; perfil: Perfil }) {
    const [usuarios, setUsuarios] = useState<Usuario[]>([]);
    const [roles, setRoles] = useState<Rol[]>([]);
    const [correo, setCorreo] = useState('');
    const [nombre, setNombre] = useState('');
    const [rol, setRol] = useState('CONSULTOR');

    async function cargar() {
        const data = await api<Paginacion<Usuario>>('/usuarios');
        const r = await api<Paginacion<Rol>>('/roles');
        setUsuarios(data.datos);
        setRoles(r.datos);
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, []);

    async function invitar(evento: FormEvent) {
        evento.preventDefault();
        try {
            await api('/usuarios', {
                method: 'POST',
                body: JSON.stringify({ correo, nombre, roles: [rol] }),
            });
            setCorreo('');
            setNombre('');
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function cambiarRol(usuarioId: string, codigo: string) {
        try {
            await api(`/usuarios/${usuarioId}/roles`, { method: 'PUT', body: JSON.stringify({ roles: [codigo] }) });
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function alternar(usuario: Usuario) {
        try {
            const accion = usuario.estado === 'activo' ? 'suspender' : 'reactivar';
            await api(`/usuarios/${usuario.id}/${accion}`, { method: 'POST' });
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Usuarios y roles</h1>
                <p className="text-sm text-slate-500">RBAC de organización · el consultor no entra aquí</p>
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
            <div className="grid gap-3">
                {usuarios.map((u) => (
                    <article key={u.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-mist bg-white p-5">
                        <div>
                            <p className="font-semibold">
                                {u.nombre} {u.apellido}
                            </p>
                            <p className="text-xs text-slate-500">{u.correo}</p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            {badge(u.estado)}
                            {perfil.permisos.includes('usuarios.actualizar') && (
                                <select
                                    className="h-9 rounded-lg border px-2 text-sm"
                                    value={u.roles[0]?.codigo}
                                    onChange={(e) => void cambiarRol(u.id, e.target.value)}
                                >
                                    {roles.map((r) => (
                                        <option key={r.id} value={r.codigo}>
                                            {r.nombre}
                                        </option>
                                    ))}
                                </select>
                            )}
                            {perfil.permisos.includes('usuarios.suspender') && (
                                <button type="button" className="text-sm text-royal" onClick={() => void alternar(u)}>
                                    {u.estado === 'activo' ? 'Suspender' : 'Reactivar'}
                                </button>
                            )}
                        </div>
                    </article>
                ))}
            </div>
            <article className="rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-display text-xl font-bold">Roles de sistema</h2>
                <ul className="mt-3 grid gap-2 text-sm">
                    {roles.map((r) => (
                        <li key={r.id}>
                            <span className="font-semibold">{r.nombre}</span> · {r.codigo} · {r.permisos?.length ?? 0} permisos
                        </li>
                    ))}
                </ul>
            </article>
        </div>
    );
}
