import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { capturar } from '../ui';
import type { Conversacion, Mensaje, Paginacion } from '../types';

export default function AsistentePage({ onError }: { onError: (m: string) => void }) {
    const [conversaciones, setConversaciones] = useState<Conversacion[]>([]);
    const [actual, setActual] = useState<Conversacion | null>(null);
    const [pregunta, setPregunta] = useState('¿Cuáles son los requisitos para iniciar un procedimiento administrativo?');
    const [enviando, setEnviando] = useState(false);

    async function cargarLista() {
        const data = await api<Paginacion<Conversacion>>('/conversaciones');
        setConversaciones(data.datos);
    }

    useEffect(() => {
        void cargarLista().catch((e) => capturar(e, onError));
    }, []);

    async function nueva() {
        try {
            const conv = await api<Conversacion>('/conversaciones', {
                method: 'POST',
                body: JSON.stringify({ titulo: 'Consulta documental' }),
            });
            setActual({ ...conv, mensajes: [] });
            await cargarLista();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function abrir(id: string) {
        try {
            setActual(await api<Conversacion>(`/conversaciones/${id}`));
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function preguntar(evento: FormEvent) {
        evento.preventDefault();
        setEnviando(true);

        try {
            let conversacion = actual;

            if (!conversacion) {
                conversacion = await api<Conversacion>('/conversaciones', {
                    method: 'POST',
                    body: JSON.stringify({ titulo: pregunta.slice(0, 80) }),
                });
                conversacion = { ...conversacion, mensajes: [] };
                setActual(conversacion);
                await cargarLista();
            }

            const mensaje = await api<Mensaje>(`/conversaciones/${conversacion.id}/mensajes`, {
                method: 'POST',
                body: JSON.stringify({ contenido: pregunta }),
            });
            setActual({ ...conversacion, mensajes: [...(conversacion.mensajes ?? []), mensaje] });
            setPregunta('');
        } catch (e) {
            capturar(e, onError);
        } finally {
            setEnviando(false);
        }
    }

    return (
        <div className="grid gap-6 lg:grid-cols-[280px_1fr]">
            <aside className="rounded-2xl border border-mist bg-white p-4">
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold">Conversaciones</h2>
                    <button type="button" className="text-xs text-royal" onClick={() => void nueva()}>
                        Nueva
                    </button>
                </div>
                <ul className="mt-3 grid gap-2">
                    {conversaciones.map((c) => (
                        <li key={c.id}>
                            <button type="button" onClick={() => void abrir(c.id)} className="w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-ice">
                                {c.titulo}
                            </button>
                        </li>
                    ))}
                </ul>
            </aside>
            <section className="grid gap-4">
                <div>
                    <h1 className="font-display text-3xl font-bold">Asistente RAG</h1>
                    <p className="text-sm text-slate-500">POST /conversaciones y /mensajes — simulado, sin LLM</p>
                </div>
                <div className="grid gap-3">
                    {(actual?.mensajes ?? []).map((m) => (
                        <article key={m.id} className="rounded-2xl border border-mist bg-white p-5">
                            <p className="text-xs font-semibold text-royal">Pregunta</p>
                            <p className="text-sm">{m.pregunta}</p>
                            <p className="mt-3 text-xs font-semibold text-royal">Respuesta {m.respuestaSustentada ? 'sustentada' : 'sin evidencia'}</p>
                            <p className="whitespace-pre-wrap text-sm">{m.respuesta}</p>
                        </article>
                    ))}
                </div>
                <form onSubmit={(e) => void preguntar(e)} className="grid gap-3">
                    <textarea className="min-h-24 rounded-xl border px-3 py-2" value={pregunta} onChange={(e) => setPregunta(e.target.value)} />
                    <button disabled={enviando} className="w-fit rounded-full bg-navy px-5 py-2 text-sm font-semibold text-white">
                        {enviando ? 'Consultando…' : 'Preguntar'}
                    </button>
                </form>
            </section>
        </div>
    );
}
