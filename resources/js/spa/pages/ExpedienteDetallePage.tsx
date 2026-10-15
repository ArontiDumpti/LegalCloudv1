import { useEffect, useState, type FormEvent } from 'react';
import { api, apiBinario } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Categoria, Documento, Expediente, Paginacion, Perfil } from '../types';

type Participante = { usuarioId: string; nombre: string; correo?: string; rolEnExpediente: string };

export default function ExpedienteDetallePage({ id, onError, perfil }: { id: string; onError: (m: string) => void; perfil: Perfil }) {
    const [expediente, setExpediente] = useState<Expediente | null>(null);
    const [documentos, setDocumentos] = useState<Documento[]>([]);
    const [participantes, setParticipantes] = useState<Participante[]>([]);
    const [categorias, setCategorias] = useState<Categoria[]>([]);
    const [titulo, setTitulo] = useState('');
    const [tipo, setTipo] = useState('reglamento');
    const [archivo, setArchivo] = useState<File | null>(null);

    async function cargar() {
        const exp = await api<Expediente>(`/expedientes/${id}`);
        setExpediente(exp);
        const docs = await api<Paginacion<Documento>>(`/expedientes/${id}/documentos`);
        setDocumentos(docs.datos);
        const parts = await api<{ datos: Participante[] }>(`/expedientes/${id}/participantes`);
        setParticipantes(parts.datos);
        const cats = await api<Paginacion<Categoria>>('/categorias');
        setCategorias(cats.datos);
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

        try {
            const reserva = await api<{
                documento: Documento;
                version: { id: string };
                carga: { url: string };
            }>(`/expedientes/${id}/documentos`, {
                method: 'POST',
                body: JSON.stringify({
                    titulo,
                    tipo,
                    origen: 'digital',
                    categoriaId: expediente?.categoriaId,
                    nombreArchivo: archivo.name,
                    tipoMime: archivo.type || 'application/pdf',
                    tamanoBytes: archivo.size,
                }),
            });
            await apiBinario(reserva.carga.url, archivo, archivo.type || 'application/pdf');
            await api(`/documentos/${reserva.documento.id}/versiones/${reserva.version.id}/confirmar`, { method: 'POST' });
            setTitulo('');
            setArchivo(null);
            await cargar();
        } catch (e) {
            capturar(e, onError);
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
            {perfil.permisos.includes('documentos.crear') && (
                <form onSubmit={(e) => void subir(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Cargar documento (URL prefirmada)</h2>
                    <input className="h-10 rounded-lg border px-3" placeholder="Título" value={titulo} onChange={(e) => setTitulo(e.target.value)} required />
                    <select className="h-10 rounded-lg border px-3" value={tipo} onChange={(e) => setTipo(e.target.value)}>
                        {['reglamento', 'resolucion', 'informe', 'acta', 'contrato', 'oficio', 'otro'].map((t) => (
                            <option key={t}>{t}</option>
                        ))}
                    </select>
                    <input type="file" accept=".pdf,.txt" onChange={(e) => setArchivo(e.target.files?.[0] ?? null)} />
                    <button className="w-fit rounded-full bg-royal px-4 py-2 text-sm font-semibold text-white">Iniciar + confirmar carga</button>
                    <p className="text-xs text-slate-500">POST documentos → PUT carga → POST confirmar. Categorías: {categorias.length}</p>
                </form>
            )}
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
