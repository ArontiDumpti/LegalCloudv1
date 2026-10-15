import { useEffect, useRef, useState, type FormEvent, type KeyboardEvent } from 'react';
import { api } from '../api';
import { capturar, ir } from '../ui';
import type { Conversacion, Mensaje, Paginacion } from '../types';

const sugeridas = [
    '¿Cuáles son los requisitos para iniciar un procedimiento administrativo?',
    '¿Qué plazos aplican para la presentación de recursos?',
    '¿Quién puede firmar la solicitud?',
];

type ResultadoConsulta = {
    resulttext: string;
    fuentes?: Array<{ titulo?: string; fragmento?: string; documentoId?: string | null }>;
};

export default function AsistentePage({ onError }: { onError: (m: string) => void }) {
    const [conversaciones, setConversaciones] = useState<Conversacion[]>([]);
    const [filtro, setFiltro] = useState('');
    const [actual, setActual] = useState<Conversacion | null>(null);
    const [pregunta, setPregunta] = useState('');
    const [enviando, setEnviando] = useState(false);
    const [abierta, setAbierta] = useState<string | null>(null);
    const finalRef = useRef<HTMLDivElement>(null);

    const mensajes = actual?.mensajes ?? [];
    const historial = conversaciones.filter((c) => c.titulo.toLowerCase().includes(filtro.toLowerCase()));

    async function cargarLista() {
        const data = await api<Paginacion<Conversacion>>('/conversaciones?limite=40');
        setConversaciones(data.datos);
    }

    useEffect(() => {
        void cargarLista().catch((e) => capturar(e, onError));
    }, []);

    useEffect(() => {
        finalRef.current?.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }, [mensajes.length, enviando]);

    function nueva() {
        setAbierta(null);
        setActual({ id: 'sesion', titulo: 'Nueva consulta', mensajes: [], creadoEn: new Date().toISOString() });
        setPregunta('');
    }

    async function abrir(id: string) {
        try {
            const conv = await api<Conversacion>(`/conversaciones/${id}`);
            setAbierta(id);
            setActual(conv);
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function consultar(texto: string) {
        const limpia = texto.trim();
        if (!limpia || enviando) {
            return;
        }

        setEnviando(true);
        setPregunta('');

        try {
            const sesion = actual ?? { id: 'sesion', titulo: limpia.slice(0, 80), mensajes: [], creadoEn: new Date().toISOString() };
            if (!actual) {
                setActual(sesion);
            }

            const rag = await api<ResultadoConsulta>('/queries', {
                method: 'POST',
                body: JSON.stringify({ consulta: limpia }),
            });

            const mensaje: Mensaje = {
                id: crypto.randomUUID(),
                pregunta: limpia,
                respuesta: rag.resulttext,
                respuestaSustentada: Boolean(rag.fuentes?.length),
                fuentes: (rag.fuentes ?? []).map((fuente) => ({
                    titulo: fuente.titulo ?? 'Fuente',
                    fragmento: fuente.fragmento ?? '',
                    documentoId: fuente.documentoId ?? null,
                })),
                creadoEn: new Date().toISOString(),
            };

            setActual({
                ...sesion,
                titulo: sesion.titulo || limpia.slice(0, 80),
                mensajes: [...(sesion.mensajes ?? []), mensaje],
            });
            await cargarLista();
        } catch (e) {
            capturar(e, onError);
            setPregunta(limpia);
        } finally {
            setEnviando(false);
        }
    }

    function enviar(evento: FormEvent) {
        evento.preventDefault();
        void consultar(pregunta);
    }

    function tecla(evento: KeyboardEvent<HTMLTextAreaElement>) {
        if (evento.key === 'Enter' && !evento.shiftKey) {
            evento.preventDefault();
            void consultar(pregunta);
        }
    }

    return (
        <div className="grid min-h-[calc(100vh-6rem)] gap-6 lg:grid-cols-[280px_1fr]">
            <aside className="flex flex-col rounded-2xl border border-mist bg-white p-4">
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold">Consultas</h2>
                    <button type="button" className="text-xs font-semibold text-royal" onClick={nueva}>
                        Nueva
                    </button>
                </div>
                <input
                    className="mt-3 h-9 rounded-lg border px-3 text-sm"
                    placeholder="Buscar consulta"
                    value={filtro}
                    onChange={(e) => setFiltro(e.target.value)}
                />
                <ul className="mt-3 grid max-h-[28rem] gap-1 overflow-y-auto">
                    {historial.map((c) => (
                        <li key={c.id}>
                            <button
                                type="button"
                                onClick={() => void abrir(c.id)}
                                className={`w-full rounded-xl px-3 py-2 text-left text-sm ${abierta === c.id ? 'bg-ice font-semibold text-royal' : 'hover:bg-ice'}`}
                            >
                                <span className="line-clamp-2">{c.titulo}</span>
                            </button>
                        </li>
                    ))}
                    {historial.length === 0 && <li className="px-2 py-6 text-center text-xs text-slate-500">Aún no hay consultas.</li>}
                </ul>
            </aside>

            <section className="flex min-h-[36rem] flex-col overflow-hidden rounded-2xl border border-mist bg-white">
                <div className="border-b border-mist px-5 py-4">
                    <h1 className="font-display text-2xl font-bold">Asistente</h1>
                    <p className="text-sm text-slate-500">Pregunte sobre los documentos y vea las fuentes.</p>
                </div>

                <div className="flex-1 space-y-4 overflow-y-auto p-5">
                    {mensajes.length === 0 && !enviando && (
                        <div className="grid place-items-center py-8 text-center">
                            <div className="max-w-md">
                                <p className="font-display text-xl font-bold">¿En qué documento necesita ayuda?</p>
                                <p className="mt-2 text-sm text-slate-500">Escriba una pregunta o elija una sugerencia para empezar.</p>
                                <div className="mt-5 grid gap-2">
                                    {sugeridas.map((item) => (
                                        <button
                                            key={item}
                                            type="button"
                                            onClick={() => void consultar(item)}
                                            className="rounded-xl border border-mist px-4 py-3 text-left text-sm hover:border-sky hover:bg-ice"
                                        >
                                            {item}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}

                    {mensajes.map((m) => (
                        <div key={m.id} className="grid gap-3">
                            <div className="ml-auto max-w-[85%] rounded-2xl bg-navy px-4 py-3 text-sm text-white">{m.pregunta}</div>
                            <div className="max-w-[92%] rounded-2xl bg-ice px-4 py-3 text-sm whitespace-pre-wrap text-slate-700">{m.respuesta}</div>
                            {m.fuentes.length > 0 && (
                                <div className="grid max-w-[92%] gap-2">
                                    {m.fuentes.map((fuente, indice) => (
                                        <button
                                            key={`${m.id}-${indice}`}
                                            type="button"
                                            onClick={() => fuente.documentoId && ir(`/documentos/${fuente.documentoId}`)}
                                            className="rounded-xl border border-mist bg-white px-3 py-2 text-left text-xs hover:border-sky"
                                        >
                                            <p className="font-semibold text-royal">
                                                Fuente {indice + 1}: {fuente.titulo}
                                            </p>
                                            {fuente.fragmento && <p className="mt-1 line-clamp-3 text-slate-600">{fuente.fragmento}</p>}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))}

                    {enviando && (
                        <div className="max-w-[70%] rounded-2xl bg-ice px-4 py-3 text-sm text-slate-500">Buscando en los documentos…</div>
                    )}
                    <div ref={finalRef} />
                </div>

                <form onSubmit={enviar} className="grid gap-3 border-t border-mist p-4">
                    <textarea
                        className="min-h-20 rounded-xl border px-3 py-2 text-sm"
                        placeholder="Escriba su pregunta. Enter envía, Mayús+Enter hace un salto de línea."
                        value={pregunta}
                        onChange={(e) => setPregunta(e.target.value)}
                        onKeyDown={tecla}
                    />
                    <button
                        disabled={enviando || !pregunta.trim()}
                        className="w-fit rounded-full bg-navy px-5 py-2 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        {enviando ? 'Consultando…' : 'Preguntar'}
                    </button>
                </form>
            </section>
        </div>
    );
}
