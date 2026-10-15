import { useState, type FormEvent } from 'react';
import { api, guardarTokens } from '../api';
import type { Token } from '../types';

const demos = [
    { nombre: 'Armas López Tatiana de Fátima Lucía', correo: 'admin@legalcloud.com', rol: 'Administradora' },
    { nombre: 'Arteaga Benites Almendra Nicolle', correo: 'documental@legalcloud.com', rol: 'Responsable documental' },
    { nombre: 'Domínguez Alba Carlos Daniel', correo: 'analista@legalcloud.com', rol: 'Especialista' },
    { nombre: 'Lozano Díaz Omar Jack', correo: 'consultor@legalcloud.com', rol: 'Consultor' },
    { nombre: 'Segura Venturo Williams Aaron', correo: 'williams@legalcloud.com', rol: 'Administrador' },
];

export default function LoginPage({ onSesion }: { onSesion: (token: Token) => Promise<void> }) {
    const [correo, setCorreo] = useState('consultor@legalcloud.com');
    const [contrasena, setContrasena] = useState('password');
    const [error, setError] = useState<string | null>(null);
    const [enviando, setEnviando] = useState(false);

    async function enviar(evento: FormEvent) {
        evento.preventDefault();
        setEnviando(true);
        setError(null);

        try {
            const token = await api<Token>('/auth/iniciar-sesion', {
                method: 'POST',
                body: JSON.stringify({ correo, contrasena }),
            });
            guardarTokens(token);
            await onSesion(token);
        } catch (e) {
            setError(e instanceof Error ? e.message : 'No se pudo iniciar sesión');
        } finally {
            setEnviando(false);
        }
    }

    return (
        <div className="mx-auto grid min-h-screen max-w-5xl items-center gap-10 px-6 py-10 lg:grid-cols-2">
            <div>
                <p className="text-xs font-semibold tracking-[0.22em] text-royal uppercase">API First · /api/v1</p>
                <h1 className="font-display mt-3 text-4xl font-bold">Ingresar a LegalCloud AI</h1>
                <p className="mt-3 text-sm text-slate-600">
                    Este frontend habla solo con el contrato OpenAPI. Autenticación JWT, RBAC y los casos de uso UC-01 a UC-21.
                </p>
                <ul className="mt-6 grid gap-2">
                    {demos.map((persona) => (
                        <li key={persona.correo}>
                            <button
                                type="button"
                                onClick={() => setCorreo(persona.correo)}
                                className="w-full rounded-xl border border-mist bg-white px-4 py-3 text-left hover:border-sky"
                            >
                                <p className="text-sm font-semibold">{persona.nombre}</p>
                                <p className="text-xs text-slate-500">
                                    {persona.rol} · {persona.correo} · password
                                </p>
                            </button>
                        </li>
                    ))}
                </ul>
            </div>
            <form onSubmit={(e) => void enviar(e)} className="rounded-3xl border border-mist bg-white p-8 shadow-sm">
                <label className="grid gap-2 text-sm">
                    Correo
                    <input className="h-11 rounded-xl border border-mist px-3" value={correo} onChange={(e) => setCorreo(e.target.value)} />
                </label>
                <label className="mt-4 grid gap-2 text-sm">
                    Contraseña
                    <input className="h-11 rounded-xl border border-mist px-3" type="password" value={contrasena} onChange={(e) => setContrasena(e.target.value)} />
                </label>
                {error && <p className="mt-4 text-sm text-red-600">{error}</p>}
                <button disabled={enviando} className="mt-6 w-full rounded-full bg-navy py-3 text-sm font-semibold text-white">
                    {enviando ? 'Ingresando…' : 'Iniciar sesión'}
                </button>
            </form>
        </div>
    );
}
