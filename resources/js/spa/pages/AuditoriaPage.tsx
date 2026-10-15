import { useEffect, useState } from 'react';
import { api } from '../api';
import { badge, capturar } from '../ui';
import type { EventoAuditoria, Paginacion } from '../types';

export default function AuditoriaPage({ onError }: { onError: (m: string) => void }) {
    const [eventos, setEventos] = useState<EventoAuditoria[]>([]);

    useEffect(() => {
        void api<Paginacion<EventoAuditoria>>('/auditoria')
            .then((data) => setEventos(data.datos))
            .catch((e) => capturar(e, onError));
    }, []);

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Auditoría</h1>
                <p className="text-sm text-slate-500">GET /api/v1/auditoria</p>
            </div>
            <div className="grid gap-3">
                {eventos.map((e) => (
                    <article key={e.id} className="rounded-2xl border border-mist bg-white p-5">
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <p className="font-semibold">{e.accion}</p>
                            {badge(e.resultado)}
                        </div>
                        <p className="mt-1 text-sm text-slate-600">{e.metadatos?.descripcion}</p>
                        <p className="mt-1 text-xs text-slate-500">
                            {e.actorCorreo} · {e.recursoTipo} · {e.ocurridoEn}
                        </p>
                    </article>
                ))}
            </div>
        </div>
    );
}
