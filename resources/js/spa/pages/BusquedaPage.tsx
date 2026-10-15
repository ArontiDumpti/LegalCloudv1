import { useState, type FormEvent } from 'react';
import { api } from '../api';
import { capturar, ir } from '../ui';
import type { ResultadoBusqueda } from '../types';

export default function BusquedaPage({ onError }: { onError: (m: string) => void }) {
    const [consulta, setConsulta] = useState('requisitos para obtener la licencia');
    const [resultado, setResultado] = useState<ResultadoBusqueda | null>(null);

    async function buscar(evento: FormEvent) {
        evento.preventDefault();
        try {
            const data = await api<ResultadoBusqueda>('/busquedas', {
                method: 'POST',
                body: JSON.stringify({ consulta, modo: 'hibrida', limite: 10 }),
            });
            setResultado(data);
        } catch (e) {
            capturar(e, onError);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Búsqueda</h1>
                <p className="text-sm text-slate-500">POST /api/v1/busquedas — evidencia persistida</p>
            </div>
            <form onSubmit={(e) => void buscar(e)} className="flex gap-3">
                <input className="h-11 flex-1 rounded-xl border px-3" value={consulta} onChange={(e) => setConsulta(e.target.value)} />
                <button className="rounded-full bg-navy px-5 text-sm font-semibold text-white">Buscar</button>
            </form>
            {resultado && (
                <div className="grid gap-3">
                    {resultado.resultados.map((item, i) => (
                        <button key={`${item.documento.id}-${i}`} type="button" onClick={() => ir(`/documentos/${item.documento.id}`)} className="rounded-2xl border border-mist bg-white p-5 text-left">
                            <p className="font-semibold">{item.documento.titulo}</p>
                            <p className="mt-2 text-sm text-slate-600">{item.fragmento}</p>
                            <p className="mt-2 text-xs text-royal">Puntaje {item.puntaje}</p>
                        </button>
                    ))}
                    {resultado.resultados.length === 0 && <p className="text-sm text-slate-500">Sin coincidencias en el acervo indexado.</p>}
                </div>
            )}
        </div>
    );
}
