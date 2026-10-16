import { useEffect, useState } from 'react';
import { api } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Documento, Paginacion, Perfil } from '../types';

type Version = { id: string; numero: number; estado: string; esVigente: boolean; notas?: string };
type Texto = { datos: Array<{ pagina: number; texto: string }> };
type Descarga = { url: string; nombreArchivo?: string };

export default function DocumentoDetallePage({ id, onError, perfil }: { id: string; onError: (m: string) => void; perfil: Perfil }) {
    const [doc, setDoc] = useState<Documento | null>(null);
    const [versiones, setVersiones] = useState<Version[]>([]);
    const [texto, setTexto] = useState('');

    async function cargar() {
        const documento = await api<Documento>(`/documentos/${id}`);
        setDoc(documento);
        const vers = await api<Paginacion<Version>>(`/documentos/${id}/versiones`);
        setVersiones(vers.datos);
        const vigente = vers.datos.find((v) => v.esVigente) ?? vers.datos[0];
        if (vigente) {
            const paginas = await api<Texto>(`/documentos/${id}/versiones/${vigente.id}/texto`);
            setTexto(paginas.datos.map((p) => p.texto).join('\n\n'));
        }
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, [id]);

    async function descargar(versionId: string) {
        try {
            const data = await api<Descarga>(`/documentos/${id}/versiones/${versionId}/descarga`);
            window.open(data.url, '_blank');
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function reprocesar(versionId: string) {
        try {
            await api(`/documentos/${id}/versiones/${versionId}/procesamiento`, { method: 'POST' });
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    if (!doc) {
        return <p className="text-sm text-slate-500">Cargando documento…</p>;
    }

    return (
        <div className="grid gap-6">
            <button type="button" className="text-sm text-royal" onClick={() => ir('/documentos')}>
                ← Documentos
            </button>
            <div>
                <h1 className="font-display text-3xl font-bold">{doc.titulo}</h1>
                <div className="mt-2 flex flex-wrap gap-2">
                    {doc.categoriaNombre && badge(doc.categoriaNombre)}
                    {badge(doc.estadoProcesamiento)}
                </div>
            </div>
            <article className="rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-display text-xl font-bold">Versiones</h2>
                <ul className="mt-3 grid gap-2">
                    {versiones.map((v) => (
                        <li key={v.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-mist px-4 py-3 text-sm">
                            <span>
                                v{v.numero} · {v.estado} {v.esVigente ? '· vigente' : ''}
                            </span>
                            <span className="flex gap-2">
                                {perfil.permisos.includes('documentos.descargar') && (
                                    <button type="button" className="text-royal" onClick={() => void descargar(v.id)}>
                                        Descargar
                                    </button>
                                )}
                                {perfil.permisos.includes('documentos.actualizar') && (
                                    <button type="button" className="text-royal" onClick={() => void reprocesar(v.id)}>
                                        Reprocesar OCR
                                    </button>
                                )}
                            </span>
                        </li>
                    ))}
                </ul>
            </article>
            <article className="rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-display text-xl font-bold">Texto extraído</h2>
                <pre className="mt-3 max-h-[420px] overflow-auto whitespace-pre-wrap text-sm text-slate-700">{texto || 'Sin texto indexado.'}</pre>
            </article>
        </div>
    );
}
