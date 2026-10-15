import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar, ir } from '../ui';
import type { Categoria, Expediente, Paginacion } from '../types';

export default function ExpedientesPage({ onError, puedeCrear }: { onError: (m: string) => void; puedeCrear: boolean }) {
    const [lista, setLista] = useState<Expediente[]>([]);
    const [categorias, setCategorias] = useState<Categoria[]>([]);
    const [titulo, setTitulo] = useState('');
    const [descripcion, setDescripcion] = useState('');
    const [categoriaId, setCategoriaId] = useState('');

    async function cargar() {
        const data = await api<Paginacion<Expediente>>('/expedientes');
        setLista(data.datos);
        const cats = await api<Paginacion<Categoria>>('/categorias');
        setCategorias(cats.datos);
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, []);

    async function crear(evento: FormEvent) {
        evento.preventDefault();
        try {
            await api('/expedientes', {
                method: 'POST',
                body: JSON.stringify({
                    titulo,
                    descripcion,
                    categoriaId: categoriaId || undefined,
                    nivelConfidencialidad: 'interno',
                }),
            });
            setTitulo('');
            setDescripcion('');
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Expedientes</h1>
                <p className="text-sm text-slate-500">Registro y seguimiento de cada carpeta documental</p>
            </div>
            {puedeCrear && (
                <form onSubmit={(e) => void crear(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5 md:grid-cols-2">
                    <input className="h-10 rounded-lg border px-3" placeholder="Título" value={titulo} onChange={(e) => setTitulo(e.target.value)} required />
                    <select className="h-10 rounded-lg border px-3" value={categoriaId} onChange={(e) => setCategoriaId(e.target.value)}>
                        <option value="">Categoría</option>
                        {categorias.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.nombre}
                            </option>
                        ))}
                    </select>
                    <textarea className="rounded-lg border px-3 py-2 md:col-span-2" placeholder="Descripción" value={descripcion} onChange={(e) => setDescripcion(e.target.value)} />
                    <button className="rounded-full bg-royal px-4 py-2 text-sm font-semibold text-white">Crear expediente</button>
                </form>
            )}
            <div className="grid gap-3">
                {lista.map((item) => (
                    <button key={item.id} type="button" onClick={() => ir(`/expedientes/${item.id}`)} className="rounded-2xl border border-mist bg-white p-5 text-left hover:border-sky">
                        <div className="flex items-center justify-between gap-3">
                            <p className="font-semibold">{item.codigo}</p>
                            {badge(item.estado)}
                        </div>
                        <p className="mt-1 text-sm">{item.titulo}</p>
                        <p className="mt-1 text-xs text-slate-500">{item.documentos} documentos</p>
                    </button>
                ))}
            </div>
        </div>
    );
}
