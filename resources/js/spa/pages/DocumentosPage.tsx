import { useEffect, useState } from 'react';
import { api } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Documento, Expediente, Paginacion } from '../types';

export default function DocumentosPage({ onError }: { onError: (m: string) => void }) {
    const [docs, setDocs] = useState<Documento[]>([]);

    useEffect(() => {
        void (async () => {
            const expedientes = await api<Paginacion<Expediente>>('/expedientes');
            const paginas = await Promise.all(expedientes.datos.map((e) => api<Paginacion<Documento>>(`/expedientes/${e.id}/documentos`)));
            setDocs(paginas.flatMap((p) => p.datos));
        })().catch((e) => capturar(e, onError));
    }, []);

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Documentos</h1>
                <p className="text-sm text-slate-500">Agregado desde GET /expedientes/&#123;id&#125;/documentos</p>
            </div>
            <div className="grid gap-3">
                {docs.map((doc) => (
                    <button key={doc.id} type="button" onClick={() => ir(`/documentos/${doc.id}`)} className="rounded-2xl border border-mist bg-white p-5 text-left hover:border-sky">
                        <div className="flex justify-between gap-3">
                            <p className="font-semibold">{doc.titulo}</p>
                            {badge(doc.estadoProcesamiento)}
                        </div>
                        <p className="mt-1 text-xs text-slate-500">{doc.tipo}</p>
                    </button>
                ))}
            </div>
        </div>
    );
}
