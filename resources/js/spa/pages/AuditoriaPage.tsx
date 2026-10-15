import { useEffect, useMemo, useState } from 'react';
import { api } from '../api';
import { badge, capturar } from '../ui';
import type { EventoAuditoria, Paginacion } from '../types';

export default function AuditoriaPage({ onError }: { onError: (m: string) => void }) {
    const [eventos, setEventos] = useState<EventoAuditoria[]>([]);
    const [busqueda, setBusqueda] = useState('');
    const [pagina, setPagina] = useState(1);
    const porPagina = 12;

    useEffect(() => {
        void api<Paginacion<EventoAuditoria>>('/auditoria')
            .then((data) => setEventos(data.datos))
            .catch((e) => capturar(e, onError));
    }, []);

    const filtrados = useMemo(() => {
        const q = busqueda.trim().toLowerCase();
        if (!q) {
            return eventos;
        }

        return eventos.filter((e) =>
            [e.accion, e.actorCorreo, e.recursoTipo, e.metadatos?.descripcion, e.metadatos?.modulo]
                .filter(Boolean)
                .some((valor) => String(valor).toLowerCase().includes(q)),
        );
    }, [eventos, busqueda]);

    const paginas = Math.max(1, Math.ceil(filtrados.length / porPagina));
    const actual = Math.min(pagina, paginas);
    const visibles = filtrados.slice((actual - 1) * porPagina, actual * porPagina);
    const desde = filtrados.length === 0 ? 0 : (actual - 1) * porPagina + 1;
    const hasta = Math.min(actual * porPagina, filtrados.length);

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Auditoría</h1>
                <p className="text-sm text-slate-500">Quién consultó, cargó o cambió un documento. El consultor no entra aquí.</p>
            </div>

            <section className="rounded-2xl border border-mist bg-white">
                <div className="border-b border-mist p-4">
                    <input
                        className="h-10 w-full rounded-lg border px-3"
                        placeholder="Buscar por usuario, acción o documento"
                        value={busqueda}
                        onChange={(e) => {
                            setBusqueda(e.target.value);
                            setPagina(1);
                        }}
                    />
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full text-left text-sm">
                        <thead className="bg-ice text-xs tracking-wide text-slate-500 uppercase">
                            <tr>
                                <th className="px-4 py-3 font-semibold">Acción</th>
                                <th className="px-4 py-3 font-semibold">Usuario</th>
                                <th className="px-4 py-3 font-semibold">Detalle</th>
                                <th className="px-4 py-3 font-semibold">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            {visibles.map((e) => (
                                <tr key={e.id} className="border-t border-mist">
                                    <td className="px-4 py-3">
                                        <p className="font-semibold">{e.accion.replaceAll('.', ' · ')}</p>
                                        <div className="mt-1">{badge(e.resultado)}</div>
                                    </td>
                                    <td className="px-4 py-3 text-slate-600">{e.actorCorreo ?? '—'}</td>
                                    <td className="px-4 py-3 text-slate-600">
                                        <p>{e.metadatos?.descripcion || e.recursoTipo}</p>
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-xs text-slate-500">{e.ocurridoEn}</td>
                                </tr>
                            ))}
                            {visibles.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-8 text-center text-sm text-slate-500">
                                        No hay eventos con esa búsqueda.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-3 border-t border-mist px-4 py-3 text-sm text-slate-600">
                    <p>
                        Mostrando {desde}–{hasta} de {filtrados.length}
                    </p>
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            className="rounded-lg border px-3 py-1.5 disabled:opacity-40"
                            disabled={actual <= 1}
                            onClick={() => setPagina((p) => Math.max(1, p - 1))}
                        >
                            Anterior
                        </button>
                        <span>
                            {actual} / {paginas}
                        </span>
                        <button
                            type="button"
                            className="rounded-lg border px-3 py-1.5 disabled:opacity-40"
                            disabled={actual >= paginas}
                            onClick={() => setPagina((p) => p + 1)}
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </section>
        </div>
    );
}
