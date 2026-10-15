import { useState, type FormEvent } from 'react';
import { api } from '../api';
import { badge, capturar } from '../ui';
import type { Perfil } from '../types';

export default function PerfilPage({ perfil, onError }: { perfil: Perfil; onError: (m: string) => void }) {
    const [actual, setActual] = useState('password');
    const [nueva, setNueva] = useState('');
    const [ok, setOk] = useState(false);

    async function cambiar(evento: FormEvent) {
        evento.preventDefault();
        try {
            await api('/auth/cambiar-contrasena', {
                method: 'POST',
                body: JSON.stringify({ contrasenaActual: actual, contrasenaNueva: nueva }),
            });
            setOk(true);
            setNueva('');
        } catch (e) {
            capturar(e, onError);
        }
    }

    return (
        <div className="grid gap-6">
            <div>
                <h1 className="font-display text-3xl font-bold">Mi perfil</h1>
                <p className="text-sm text-slate-500">Datos de su cuenta</p>
            </div>
            <article className="rounded-2xl border border-mist bg-white p-5">
                <p className="font-semibold">
                    {perfil.nombre} {perfil.apellido}
                </p>
                <p className="text-sm text-slate-500">{perfil.correo}</p>
                <div className="mt-3 flex flex-wrap gap-2">
                    {perfil.roles.map((r) => (
                        <span key={r.id}>{badge(r.nombre)}</span>
                    ))}
                </div>
            </article>
            <form onSubmit={(e) => void cambiar(e)} className="grid max-w-md gap-3 rounded-2xl border border-mist bg-white p-5">
                <h2 className="font-semibold">Cambiar contraseña</h2>
                <input className="h-10 rounded-lg border px-3" type="password" value={actual} onChange={(e) => setActual(e.target.value)} placeholder="Actual" />
                <input className="h-10 rounded-lg border px-3" type="password" value={nueva} onChange={(e) => setNueva(e.target.value)} placeholder="Nueva (mín. 8)" minLength={8} />
                <button className="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white">Guardar</button>
                {ok && <p className="text-sm text-green-700">Contraseña actualizada.</p>}
            </form>
        </div>
    );
}
