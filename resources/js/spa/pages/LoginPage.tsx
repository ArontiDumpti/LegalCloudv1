import { useState, type FormEvent } from 'react';
import { api, guardarTokens } from '../api';
import { CampoContrasena } from '../ui';
import type { Token } from '../types';

export default function LoginPage({
    onSesion,
    aviso,
}: {
    onSesion: (token: Token) => Promise<void>;
    aviso?: string | null;
}) {
    const [modo, setModo] = useState<'entrar' | 'crear'>('entrar');
    const [nombre, setNombre] = useState('');
    const [apellido, setApellido] = useState('');
    const [correo, setCorreo] = useState('');
    const [contrasena, setContrasena] = useState('');
    const [contrasenaConfirmacion, setContrasenaConfirmacion] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [enviando, setEnviando] = useState(false);

    async function enviar(evento: FormEvent) {
        evento.preventDefault();
        setEnviando(true);
        setError(null);

        try {
            if (modo === 'crear' && contrasena !== contrasenaConfirmacion) {
                setError('La confirmación de contraseña no coincide.');
                setEnviando(false);
                return;
            }

            const token =
                modo === 'crear'
                    ? await api<Token>('/auth/registrar', {
                          method: 'POST',
                          body: JSON.stringify({ nombre, apellido, correo, contrasena, contrasenaConfirmacion }),
                      })
                    : await api<Token>('/auth/iniciar-sesion', {
                          method: 'POST',
                          body: JSON.stringify({ correo, contrasena }),
                      });
            guardarTokens(token);
            await onSesion(token);
        } catch (e) {
            setError(e instanceof Error ? e.message : modo === 'crear' ? 'No se pudo crear la cuenta' : 'No se pudo iniciar sesión');
        } finally {
            setEnviando(false);
        }
    }

    return (
        <div className="flex min-h-screen flex-col bg-ice">
            <header className="flex items-center justify-between border-b border-mist bg-white px-6 py-4">
                <a href="/" className="flex items-center gap-3">
                    <span className="flex size-9 items-center justify-center rounded-lg bg-navy text-sm font-semibold text-white">LC</span>
                    <div>
                        <p className="text-sm leading-tight font-semibold">
                            LegalCloud <span className="text-royal">AI</span>
                        </p>
                        <p className="text-xs text-slate-500">Gestión documental</p>
                    </div>
                </a>
                <a href="/" className="rounded-lg border border-mist px-4 py-2 text-sm font-semibold text-navy hover:border-sky hover:bg-ice">
                    Volver al inicio
                </a>
            </header>

            <main className="flex flex-1 items-center justify-center px-4 py-10">
                <form onSubmit={(e) => void enviar(e)} className="w-full max-w-md rounded-2xl border border-mist bg-white p-8 shadow-sm">
                    <h1 className="font-display text-2xl font-bold">{modo === 'crear' ? 'Crear cuenta' : 'Iniciar sesión'}</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        {modo === 'crear' ? 'Complete nombre, apellido, correo y confirme su contraseña.' : 'Ingrese su correo y contraseña para continuar.'}
                    </p>
                    {aviso && modo === 'entrar' && (
                        <p className="mt-4 rounded-xl border border-sky bg-ice px-4 py-3 text-sm text-navy">{aviso}</p>
                    )}

                    {modo === 'crear' && (
                        <>
                            <label className="mt-6 grid gap-2 text-sm font-medium">
                                Nombre
                                <input
                                    className="h-11 rounded-lg border border-mist px-3 font-normal"
                                    autoComplete="given-name"
                                    value={nombre}
                                    onChange={(e) => setNombre(e.target.value)}
                                    required
                                />
                            </label>
                            <label className="mt-4 grid gap-2 text-sm font-medium">
                                Apellido
                                <input
                                    className="h-11 rounded-lg border border-mist px-3 font-normal"
                                    autoComplete="family-name"
                                    value={apellido}
                                    onChange={(e) => setApellido(e.target.value)}
                                    required
                                />
                            </label>
                        </>
                    )}

                    <label className={`${modo === 'crear' ? 'mt-4' : 'mt-6'} grid gap-2 text-sm font-medium`}>
                        Correo
                        <input
                            className="h-11 rounded-lg border border-mist px-3 font-normal"
                            type="email"
                            autoComplete="username"
                            value={correo}
                            onChange={(e) => setCorreo(e.target.value)}
                            required
                        />
                    </label>

                    <label className="mt-4 grid gap-2 text-sm font-medium">
                        Contraseña
                        <CampoContrasena
                            valor={contrasena}
                            onCambio={setContrasena}
                            autoComplete={modo === 'crear' ? 'new-password' : 'current-password'}
                            minLength={8}
                        />
                    </label>

                    {modo === 'crear' && (
                        <label className="mt-4 grid gap-2 text-sm font-medium">
                            Confirmación de contraseña
                            <CampoContrasena
                                valor={contrasenaConfirmacion}
                                onCambio={setContrasenaConfirmacion}
                                autoComplete="new-password"
                                minLength={8}
                            />
                        </label>
                    )}

                    {error && <p className="mt-4 text-sm text-red-600">{error}</p>}

                    <button disabled={enviando} className="mt-6 h-11 w-full rounded-lg bg-navy text-sm font-semibold text-white disabled:opacity-60">
                        {enviando ? 'Espere…' : modo === 'crear' ? 'Crear cuenta' : 'Entrar'}
                    </button>

                    <button
                        type="button"
                        className="mt-4 w-full text-center text-sm font-semibold text-royal hover:underline"
                        onClick={() => {
                            setModo(modo === 'crear' ? 'entrar' : 'crear');
                            setError(null);
                        }}
                    >
                        {modo === 'crear' ? 'Ya tengo cuenta. Iniciar sesión' : 'Crear cuenta'}
                    </button>

                    <a href="/" className="mt-3 block text-center text-sm text-slate-500 hover:underline">
                        Volver al inicio
                    </a>
                </form>
            </main>
        </div>
    );
}
