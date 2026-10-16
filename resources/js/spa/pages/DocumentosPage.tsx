import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Categoria, Documento, Expediente, Paginacion, Perfil } from '../types';

export default function DocumentosPage({ onError, perfil }: { onError: (m: string) => void; perfil: Perfil }) {
    const [docs, setDocs] = useState<Documento[]>([]);
    const [expedientes, setExpedientes] = useState<Expediente[]>([]);
    const [categorias, setCategorias] = useState<Categoria[]>([]);
    const [categoriaId, setCategoriaId] = useState('');
    const [filtroCategoria, setFiltroCategoria] = useState('');
    const [titulo, setTitulo] = useState('');
    const [archivo, setArchivo] = useState<File | null>(null);
    const [claveArchivo, setClaveArchivo] = useState(0);
    const [subiendo, setSubiendo] = useState(false);
    const [listo, setListo] = useState<string | null>(null);
    const puedeCargar = perfil.permisos.includes('documentos.crear');

    async function cargar() {
        const lista = await api<Paginacion<Expediente>>('/expedientes');
        const cats = await api<Paginacion<Categoria>>('/categorias');
        setExpedientes(lista.datos);
        setCategorias(cats.datos);
        if (!categoriaId && cats.datos[0]) {
            setCategoriaId(cats.datos[0].id);
        }
        const paginas = await Promise.all(lista.datos.map((e) => api<Paginacion<Documento>>(`/expedientes/${e.id}/documentos`)));
        setDocs(paginas.flatMap((p) => p.datos));
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, []);

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
            if (categoriaId) {
                datos.append('categoriaId', categoriaId);
            }
            datos.append('archivo', archivo);
            const creado = await api<Documento>('/documentos/archivo', {
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
                <p className="text-sm text-slate-500">Cargue un PDF y clasifíquelo por categoría</p>
            </div>

            {puedeCargar ? (
                <form onSubmit={(e) => void subir(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5">
                    <h2 className="font-display text-xl font-bold">Cargar documento</h2>
                    <p className="text-sm text-slate-500">Elija la categoría y el PDF. Se extrae el texto al guardar.</p>
                    <label className="grid gap-1 text-sm font-medium">
                        Categoría
                        <select className="h-11 rounded-lg border px-3 font-normal" value={categoriaId} onChange={(e) => setCategoriaId(e.target.value)} required>
                            {categorias.length === 0 && <option value="">No hay categorías</option>}
                            {categorias.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.nombre}
                                </option>
                            ))}
                        </select>
                    </label>
                    <input className="h-11 rounded-lg border px-3" placeholder="Título del documento" value={titulo} onChange={(e) => setTitulo(e.target.value)} />
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
                    <button disabled={subiendo || !archivo} className="h-11 w-fit rounded-full bg-royal px-5 text-sm font-semibold text-white disabled:opacity-50">
                        {subiendo ? 'Cargando…' : 'Cargar PDF'}
                    </button>
                    {listo && <p className="text-sm text-green-700">{listo}</p>}
                </form>
            ) : (
                <p className="rounded-2xl border border-mist bg-white p-5 text-sm text-slate-500">Su rol puede consultar documentos, pero no cargarlos.</p>
            )}

            <div className="grid gap-3">
                <label className="grid gap-1 text-sm font-medium">
                    Ver por categoría
                    <select className="h-11 rounded-lg border px-3 font-normal" value={filtroCategoria} onChange={(e) => setFiltroCategoria(e.target.value)}>
                        <option value="">Todas</option>
                        {categorias.map((cat) => (
                            <option key={cat.id} value={cat.id}>
                                {cat.nombre}
                            </option>
                        ))}
                    </select>
                </label>
                {docs
                    .filter((doc) => !filtroCategoria || doc.categoriaId === filtroCategoria)
                    .map((doc) => (
                    <button key={doc.id} type="button" onClick={() => ir(`/documentos/${doc.id}`)} className="rounded-2xl border border-mist bg-white p-5 text-left hover:border-sky">
                        <div className="flex justify-between gap-3">
                            <p className="font-semibold">{doc.titulo}</p>
                            {badge(doc.estadoProcesamiento)}
                        </div>
                        {doc.categoriaNombre && <p className="mt-1 text-xs text-slate-500">{doc.categoriaNombre}</p>}
                    </button>
                ))}
                {docs.filter((doc) => !filtroCategoria || doc.categoriaId === filtroCategoria).length === 0 && (
                    <p className="text-sm text-slate-500">Aún no hay documentos{filtroCategoria ? ' en esta categoría' : ''}.</p>
                )}
            </div>
        </div>
    );
}
