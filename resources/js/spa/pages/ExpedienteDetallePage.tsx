import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Documento, Expediente, Paginacion, Perfil } from '../types';

type Participante = { usuarioId: string; nombre: string; correo?: string; rolEnExpediente: string };

export default function ExpedienteDetallePage({ id, onError, perfil }: { id: string; onError: (m: string) => void; perfil: Perfil }) {
    const [expediente, setExpediente] = useState<Expediente | null>(null);
    const [documentos, setDocumentos] = useState<Documento[]>([]);
    const [participantes, setParticipantes] = useState<Participante[]>([]);
    const [titulo, setTitulo] = useState('');
    const [tipo, setTipo] = useState('reglamento');
    const [archivo, setArchivo] = useState<File | null>(null);
    const [subiendo, setSubiendo] = useState(false);
    const [listo, setListo] = useState<string | null>(null);

    async function cargar() {
        const exp = await api<Expediente>(`/expedientes/${id}`);
        setExpediente(exp);
        const docs = await api<Paginacion<Documento>>(`/expedientes/${id}/documentos`);
        setDocumentos(docs.datos);
        const parts = await api<{ datos: Participante[] }>(`/expedientes/${id}/participantes`);
        setParticipantes(parts.datos);
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, [id]);

    async function transicionar(accion: string) {
        try {
            await api(`/expedientes/${id}/transiciones`, { method: 'POST', body: JSON.stringify({ accion }) });
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    async function subir(evento: FormEvent) {
        evento.preventDefault();
        if (!archivo) {
            return;
        }

        setSubiendo(true);
        setListo(null);
        try {
            const datos = new FormData();
            datos.append('titulo', titulo || archivo.name.replace(/\.pdf$/i, ''));
            datos.append('tipo', tipo);
            datos.append('archivo', archivo);
            const creado = await api<Documento>(`/expedientes/${id}/documentos/archivo`, {
                method: 'POST',
                body: datos,
            });
            setTitulo('');
            setArchivo(null);
            setListo(`Documento listo: ${creado.titulo}`);
            setDocumentos((actual) => [creado, ...actual.filter((doc) => doc.id !== creado.id)]);
        } catch (e) {
            capturar(e, onError);
        } finally {
            setSubiendo(false);
        }
    }

    if (!expediente) {
        return <p className="text-sm text-slate-500">Cargando expediente…</p>;
    }

    return (
        <div className="grid gap-6">
            <button type="button" className="text-sm text-royal" onClick={() => ir('/expedientes')}>
                ← Expedientes
            </button>
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-xs text-royal">{expediente.codigo}</p>
                    <h1 className="font-display text-3xl font-bold">{expediente.titulo}</h1>
                    <p className="mt-2 text-sm text-slate-600">{expediente.descripcion}</p>
                </div>
                {badge(expediente.estado)}
            </div>
            {perfil.permisos.includes('expedientes.transicionar') && (
                <div className="flex flex-wrap gap-2">
                    {['abrir', 'enviar_a_revision', 'devolver', 'cerrar', 'archivar', 'reabrir'].map((accion) => (
                        <button key={accion} type="button" onClick={() => void transicionar(accion)} className="rounded-full border border-mist bg-white px-3 py-1 text-xs">
                            {accion.replaceAll('_', ' ')}
                        </button>
                    ))}
                </div>
            )}
            {perfil.permisos.includes('documentos.crear') && (
                <form onSubmit={(e) => void subir(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Cargar documento</h2>
                    <p className="text-sm text-slate-500">Suba un PDF a este expediente. El texto se extrae al guardar.</p>
                    <input className="h-11 rounded-lg border px-3" placeholder="Título" value={titulo} onChange={(e) => setTitulo(e.target.value)} required />
                    <select className="h-11 rounded-lg border px-3" value={tipo} onChange={(e) => setTipo(e.target.value)}>
                        {['reglamento', 'resolucion', 'informe', 'acta', 'contrato', 'oficio', 'otro'].map((t) => (
                            <option key={t}>{t}</option>
                        ))}
                    </select>
                    <label className="grid gap-2 text-sm font-medium">
                        Archivo PDF
                        <input className="rounded-lg border border-mist bg-ice px-3 py-3" type="file" accept=".pdf,.txt,application/pdf" onChange={(e) => setArchivo(e.target.files?.[0] ?? null)} required />
                    </label>
                    {archivo && <p className="text-xs text-slate-500">Seleccionado: {archivo.name}</p>}
                    <button disabled={subiendo || !archivo} className="h-11 w-fit rounded-full bg-royal px-5 text-sm font-semibold text-white disabled:opacity-50">
                        {subiendo ? 'Cargando…' : 'Cargar PDF'}
                    </button>
                    {listo && <p className="text-sm text-green-700">{listo}</p>}
                </form>
            )}
            <article className="rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-display text-xl font-bold">Participantes</h2>
                <ul className="mt-3 grid gap-2 text-sm">
                    {participantes.map((p) => (
                        <li key={p.usuarioId}>
                            {p.nombre} · {p.rolEnExpediente}
                        </li>
                    ))}
                </ul>
            </article>
            <article className="rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-display text-xl font-bold">Documentos</h2>
                <ul className="mt-3 grid gap-2">
                    {documentos.map((doc) => (
                        <li key={doc.id}>
                            <button type="button" onClick={() => ir(`/documentos/${doc.id}`)} className="w-full rounded-xl border border-mist px-4 py-3 text-left hover:border-sky">
                                <div className="flex justify-between gap-2">
                                    <span className="font-semibold">{doc.titulo}</span>
                                    {badge(doc.estadoProcesamiento)}
                                </div>
                            </button>
                        </li>
                    ))}
                </ul>
            </article>
        </div>
    );
}
