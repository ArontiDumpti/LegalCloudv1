import { useEffect, useState } from 'react';
import { api } from '../api';
import { badge, ir } from '../ui';
import type { Dashboard, Perfil, Usuario } from '../types';

export default function DashboardPage({ perfil }: { perfil: Perfil }) {
    const [data, setData] = useState<Dashboard | null>(null);
    const [equipo, setEquipo] = useState<Usuario[]>([]);

    useEffect(() => {
        void api<Dashboard>('/dashboard').then(setData);
        if (perfil.permisos.includes('usuarios.leer')) {
            void api<{ datos: Usuario[] }>('/usuarios?limite=6').then((r) => setEquipo(r.datos));
        }
    }, [perfil.permisos]);

    if (!data) {
        return <p className="text-sm text-slate-500">Cargando tablero…</p>;
    }

    return (
        <div className="grid gap-6">
            <div>
                <p className="text-xs font-semibold tracking-[0.2em] text-royal uppercase">LegalCloud AI</p>
                <h1 className="font-display text-3xl font-bold">Panel de gestión documental</h1>
                <p className="mt-1 text-sm text-slate-500">Resumen de expedientes, documentos y actividad reciente</p>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {Object.entries(data.expedientesPorEstado).map(([estado, valor]) => (
                    <button key={estado} type="button" onClick={() => ir('/expedientes')} className="rounded-2xl border border-mist bg-white p-5 text-left">
                        <p className="text-sm text-slate-500">Expedientes · {estado.replaceAll('_', ' ')}</p>
                        <p className="font-display mt-2 text-4xl font-bold">{valor}</p>
                    </button>
                ))}
            </div>
            <div className="grid gap-4 lg:grid-cols-2">
                <article className="rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Documentos por procesamiento</h2>
                    <ul className="mt-4 grid gap-2">
                        {Object.entries(data.documentosPorProcesamiento).map(([estado, valor]) => (
                            <li key={estado} className="flex justify-between text-sm">
                                <span>{badge(estado)}</span>
                                <span className="font-semibold">{valor}</span>
                            </li>
                        ))}
                    </ul>
                    <p className="mt-4 text-xs text-slate-500">
                        Consultas 7d: {data.consultas7d ?? 0} · Almacenamiento: {Math.round((data.almacenamientoBytes ?? 0) / 1024)} KB
                    </p>
                </article>
                <article className="rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Actividad reciente</h2>
                    <ul className="mt-4 grid gap-2 text-sm">
                        {(data.actividadReciente ?? []).map((evento) => (
                            <li key={evento.id} className="rounded-xl border border-mist px-3 py-2">
                                <p className="font-semibold">{evento.accion}</p>
                                <p className="text-xs text-slate-500">{evento.actorCorreo} · {evento.ocurridoEn}</p>
                            </li>
                        ))}
                    </ul>
                </article>
            </div>
            {equipo.length > 0 && (
                <article className="rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Equipo</h2>
                    <ul className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        {equipo.map((persona) => (
                            <li key={persona.id} className="rounded-xl border border-mist px-4 py-3">
                                <p className="font-semibold">
                                    {persona.nombre} {persona.apellido}
                                </p>
                                <p className="text-xs text-slate-500">{persona.roles[0]?.nombre}</p>
                            </li>
                        ))}
                    </ul>
                </article>
            )}
        </div>
    );
}
