import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Documento, Expediente, Paginacion, Perfil } from '../types';

export default function DocumentosPage({ onError, perfil }: { onError: (m: string) => void; perfil: Perfil }) {
    const [docs, setDocs] = useState<Documento[]>([]);
    const [expedientes, setExpedientes] = useState<Expediente[]>([]);
    const [expedienteId, setExpedienteId] = useState('');
    const [titulo, setTitulo] = useState('');
    const [tipo, setTipo] = useState('reglamento');
    const [archivo, setArchivo] = useState<File | null>(null);
    const [claveArchivo, setClaveArchivo] = useState(0);
    const [subiendo, setSubiendo] = useState(false);
    const [listo, setListo] = useState<string | null>(null);
    const puedeCargar = perfil.permisos.includes('documentos.crear');

    async function cargar() {
        const lista = await api<Paginacion<Expediente>>('/expedientes');
        setExpedientes(lista.datos);
        if (!expedienteId && lista.datos[0]) {
            setExpedienteId(lista.datos[0].id);
        }
        const paginas = await Promise.all(lista.datos.map((e) => api<Paginacion<Documento>>(`/expedientes/${e.id}/documentos`)));
        setDocs(paginas.flatMap((p) => p.datos));
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, []);

    async function subir(evento: FormEvent) {
        evento.preventDefault();
        if (!archivo || !expedienteId) {
            return;
        }

        setSubiendo(true);
        setListo(null);
        try {
            const datos = new FormData();
            datos.append('titulo', titulo || archivo.name.replace(/\.pdf$/i, ''));
            datos.append('tipo', tipo);
            datos.append('archivo', archivo);
            const creado = await api<Documento>(`/expedientes/${expedienteId}/documentos/archivo`, {
                method: 'POST',
                body: datos,
            });
            setTitulo('');
            setArchivo(null);
            setClaveArchivo((n) => n + 1);
            setListo(`Documento listo: ${creado.titulo}`);
            setDocs((actual) => [creado, ...actual.filter((doc) => doc.id !== creado.id)]);
        } catch (e) {
            capturar(e, onError);
        } finally {
            setSubiendo(false);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Documentos</h1>
                <p className="text-sm text-slate-500">Cargue un PDF y organice el acervo de los expedientes</p>
            </div>

            {puedeCargar ? (
                <form onSubmit={(e) => void subir(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Cargar documento</h2>
                    <p className="text-sm text-slate-500">Elija el expediente, el PDF y pulse Cargar. Se extrae el texto al guardar.</p>
                    <select className="h-11 rounded-lg border px-3" value={expedienteId} onChange={(e) => setExpedienteId(e.target.value)} required>
                        {expedientes.length === 0 && <option value="">No hay expedientes</option>}
                        {expedientes.map((exp) => (
                            <option key={exp.id} value={exp.id}>
                                {exp.codigo} · {exp.titulo}
                            </option>
                        ))}
                    </select>
                    <input className="h-11 rounded-lg border px-3" placeholder="Título del documento" value={titulo} onChange={(e) => setTitulo(e.target.value)} />
                    <select className="h-11 rounded-lg border px-3" value={tipo} onChange={(e) => setTipo(e.target.value)}>
                        {['reglamento', 'resolucion', 'informe', 'acta', 'contrato', 'oficio', 'otro'].map((t) => (
                            <option key={t} value={t}>
                                {t}
                            </option>
                        ))}
                    </select>
                    <label className="grid gap-2 text-sm font-medium">
                        Archivo PDF
                        <input
                            className="rounded-lg border border-mist bg-ice px-3 py-3"
                            key={claveArchivo}
                            type="file"
                            accept=".pdf,.txt,application/pdf"
                            onChange={(e) => setArchivo(e.target.files?.[0] ?? null)}
                            required
                        />
                    </label>
                    {archivo && <p className="text-xs text-slate-500">Seleccionado: {archivo.name}</p>}
                    <button disabled={subiendo || !archivo || !expedienteId} className="h-11 w-fit rounded-full bg-royal px-5 text-sm font-semibold text-white disabled:opacity-50">
                        {subiendo ? 'Cargando…' : 'Cargar PDF'}
                    </button>
                    {listo && <p className="text-sm text-green-700">{listo}</p>}
                </form>
            ) : (
                <p className="rounded-2xl border border-mist bg-white p-5 text-sm text-slate-500">Su rol puede consultar documentos, pero no cargarlos.</p>
            )}

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
                {docs.length === 0 && <p className="text-sm text-slate-500">Aún no hay documentos.</p>}
            </div>
        </div>
    );
}
