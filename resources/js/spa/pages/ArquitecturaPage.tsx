import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { capturar } from '../ui';
import type { Documento, Expediente, Paginacion, Perfil } from '../types';

type ResultadoConsulta = {
    resulttext: string;
    modelo?: string;
    fuentes?: Array<{ titulo?: string; codigo?: string; texto?: string }>;
};

const etapas = [
    { nombre: 'Archivo', funcion: 'Recibe el documento del expediente.' },
    { nombre: 'Texto', funcion: 'Extrae el contenido para poder consultarlo.' },
    { nombre: 'Índice', funcion: 'Deja el documento listo para la búsqueda.' },
    { nombre: 'Búsqueda', funcion: 'Encuentra los fragmentos más cercanos.' },
    { nombre: 'Respuesta', funcion: 'Redacta la respuesta con esas fuentes.' },
];

export default function ArquitecturaPage({ onError, perfil }: { onError: (m: string) => void; perfil: Perfil }) {
    const [documentos, setDocumentos] = useState<Documento[]>([]);
    const [documentoId, setDocumentoId] = useState('');
    const [consulta, setConsulta] = useState('¿Cuáles son los requisitos para iniciar un procedimiento administrativo?');
    const [ingesta, setIngesta] = useState<string | null>(null);
    const [resultado, setResultado] = useState<ResultadoConsulta | null>(null);
    const [ocupado, setOcupado] = useState(false);
    const puedeIngerir = perfil.permisos.includes('documentos.actualizar');

    useEffect(() => {
        void (async () => {
            const expedientes = await api<Paginacion<Expediente>>('/expedientes');
            const paginas = await Promise.all(expedientes.datos.map((e) => api<Paginacion<Documento>>(`/expedientes/${e.id}/documentos`)));
            const lista = paginas.flatMap((p) => p.datos);
            setDocumentos(lista);
            if (lista[0]) {
                setDocumentoId(lista[0].id);
            }
        })().catch((e) => capturar(e, onError));
    }, []);

    async function indexar(evento: FormEvent) {
        evento.preventDefault();
        if (!documentoId) {
            return;
        }
        setOcupado(true);
        try {
            const data = await api<{ fragmentos: number; estado: string; ocr?: string; embeddings?: string; modelo?: string }>('/ingestions', {
                method: 'POST',
                body: JSON.stringify({ documentoId }),
            });
            setIngesta(`Documento listo: ${data.fragmentos} fragmentos.`);
        } catch (e) {
            capturar(e, onError);
        } finally {
            setOcupado(false);
        }
    }

    async function preguntar(evento: FormEvent) {
        evento.preventDefault();
        setOcupado(true);
        try {
            setResultado(
                await api<ResultadoConsulta>('/queries', {
                    method: 'POST',
                    body: JSON.stringify({ consulta }),
                }),
            );
        } catch (e) {
            capturar(e, onError);
        } finally {
            setOcupado(false);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Arquitectura</h1>
                <p className="text-sm text-slate-500">Procese un archivo y consulte los documentos indexados.</p>
            </div>

            <div className="grid gap-3 md:grid-cols-5">
                {etapas.map((etapa) => (
                    <article key={etapa.nombre} className="rounded-2xl border border-mist bg-white p-4">
                        <p className="text-sm font-semibold">{etapa.nombre}</p>
                        <p className="mt-2 text-xs leading-relaxed text-slate-600">{etapa.funcion}</p>
                    </article>
                ))}
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
                <form onSubmit={(e) => void indexar(e)} className="rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Ingesta</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        {puedeIngerir
                            ? 'Procesa el archivo para que se pueda buscar y consultar.'
                            : 'Su rol puede consultar; la indexación la hace un especialista o administrador.'}
                    </p>
                    <select className="mt-4 h-11 w-full rounded-lg border px-3" value={documentoId} onChange={(e) => setDocumentoId(e.target.value)}>
                        {documentos.map((doc) => (
                            <option key={doc.id} value={doc.id}>
                                {doc.titulo}
                            </option>
                        ))}
                    </select>
                    <button disabled={ocupado || !documentoId || !puedeIngerir} className="mt-4 rounded-lg bg-navy px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">
                        Procesar archivo
                    </button>
                    {ingesta && <p className="mt-3 text-sm text-slate-600">{ingesta}</p>}
                </form>

                <form onSubmit={(e) => void preguntar(e)} className="rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Consulta</h2>
                    <p className="mt-1 text-sm text-slate-500">Pregunte sobre los documentos y vea las fuentes.</p>
                    <textarea className="mt-4 min-h-24 w-full rounded-lg border px-3 py-2" value={consulta} onChange={(e) => setConsulta(e.target.value)} />
                    <button disabled={ocupado} className="mt-4 rounded-lg bg-royal px-4 py-2 text-sm font-semibold text-white">
                        Consultar
                    </button>
                    {resultado && (
                        <div className="mt-4 text-sm">
                            <p className="whitespace-pre-wrap">{resultado.resulttext}</p>
                            <p className="mt-2 text-xs text-slate-500">{resultado.modelo}</p>
                            {resultado.fuentes && resultado.fuentes.length > 0 && (
                                <ul className="mt-3 grid gap-2 text-xs text-slate-600">
                                    {resultado.fuentes.map((fuente, indice) => (
                                        <li key={`${fuente.codigo ?? fuente.titulo ?? 'fuente'}-${indice}`}>
                                            {fuente.codigo ?? fuente.titulo}: {fuente.texto}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    )}
                </form>
            </div>
        </div>
    );
}
