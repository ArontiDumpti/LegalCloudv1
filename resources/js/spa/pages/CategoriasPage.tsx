import { useEffect, useState, type FormEvent } from 'react';
import { api } from '../api';
import { capturar } from '../ui';
import type { Categoria, Paginacion } from '../types';

export default function CategoriasPage({ onError, puedeCrear }: { onError: (m: string) => void; puedeCrear: boolean }) {
    const [lista, setLista] = useState<Categoria[]>([]);
    const [codigo, setCodigo] = useState('');
    const [nombre, setNombre] = useState('');
    const [descripcion, setDescripcion] = useState('');

    async function cargar() {
        const data = await api<Paginacion<Categoria>>('/categorias');
        setLista(data.datos);
    }

    useEffect(() => {
        void cargar().catch((e) => capturar(e, onError));
    }, []);

    async function crear(evento: FormEvent) {
        evento.preventDefault();
        try {
            await api('/categorias', { method: 'POST', body: JSON.stringify({ codigo, nombre, descripcion }) });
            setCodigo('');
            setNombre('');
            setDescripcion('');
            await cargar();
        } catch (e) {
            capturar(e, onError);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Categorías</h1>
                <p className="text-sm text-slate-500">Clasificación de expedientes y documentos</p>
            </div>
            {puedeCrear && (
                <form onSubmit={(e) => void crear(e)} className="grid gap-3 rounded-2xl border border-mist bg-white p-5 md:grid-cols-3">
                    <input className="h-10 rounded-lg border px-3" placeholder="Código" value={codigo} onChange={(e) => setCodigo(e.target.value)} required />
                    <input className="h-10 rounded-lg border px-3" placeholder="Nombre" value={nombre} onChange={(e) => setNombre(e.target.value)} required />
                    <input className="h-10 rounded-lg border px-3" placeholder="Descripción" value={descripcion} onChange={(e) => setDescripcion(e.target.value)} />
                    <button className="rounded-full bg-royal px-4 py-2 text-sm font-semibold text-white">Crear</button>
                </form>
            )}
            <div className="grid gap-3 md:grid-cols-2">
                {lista.map((c) => (
                    <article key={c.id} className="rounded-2xl border border-mist bg-white p-5">
                        <p className="text-xs text-royal">{c.codigo}</p>
                        <h2 className="font-semibold">{c.nombre}</h2>
                        <p className="text-sm text-slate-500">{c.descripcion}</p>
                    </article>
                ))}
            </div>
        </div>
    );
}
