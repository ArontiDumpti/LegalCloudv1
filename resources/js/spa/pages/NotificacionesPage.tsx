import { useEffect, useState } from 'react';
import { api } from '../api';
import { capturar } from '../ui';
import type { Notificacion, Paginacion } from '../types';

export default function NotificacionesPage({ onError }: { onError: (m: string) => void }) {
    const [lista, setLista] = useState<Notificacion[]>([]);
    const [conteo, setConteo] = useState(0);

    async function cargar() {
        const data = await api<Paginacion<Notificacion>>('/notificaciones');
        const noLeidas = await api<{ conteo: number }>('/notificaciones/no-leidas/conteo');
        setLista(data.datos);
        setConteo(noLeidas.conteo);
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, []);

    async function leer(id: string) {
        try {
            await api(`/notificaciones/${id}/lectura`, { method: 'POST' });
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function leerTodas() {
        try {
            await api('/notificaciones/lectura', { method: 'POST' });
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    return (
        <div className="grid gap-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="font-display text-3xl font-bold">Notificaciones</h1>
                    <p className="text-sm text-slate-500">{conteo} sin leer</p>
                </div>
                <button type="button" className="text-sm text-royal" onClick={() => void leerTodas()}>
                    Marcar todas
                </button>
            </div>
            <div className="grid gap-3">
                {lista.map((n) => (
                    <article key={n.id} className={`rounded-2xl border p-5 ${n.leida ? 'border-mist bg-white' : 'border-sky bg-ice'}`}>
                        <p className="font-semibold">{n.titulo}</p>
                        <p className="text-sm text-slate-600">{n.mensaje}</p>
                        {!n.leida && (
                            <button type="button" className="mt-2 text-xs text-royal" onClick={() => void leer(n.id)}>
                                Marcar leída
                            </button>
                        )}
                    </article>
                ))}
            </div>
        </div>
    );
}
