import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { ApiError } from './api';

export function ir(path: string): void {
    const destino = path.startsWith('/app') ? path : `/app${path.startsWith('/') ? path : `/${path}`}`;
    window.history.pushState({}, '', destino);
    window.dispatchEvent(new Event('spa:nav'));
}

export function badge(valor: string) {
    return <span className="rounded-full bg-ice px-2 py-0.5 text-[11px] font-semibold text-royal">{valor.replaceAll('_', ' ')}</span>;
}

export function CampoContrasena({
    valor,
    onCambio,
    placeholder,
    autoComplete,
    minLength,
    requerido = true,
}: {
    valor: string;
    onCambio: (valor: string) => void;
    placeholder?: string;
    autoComplete?: string;
    minLength?: number;
    requerido?: boolean;
}) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="relative">
            <input
                className="h-11 w-full rounded-lg border border-mist px-3 pr-12 font-normal"
                type={visible ? 'text' : 'password'}
                autoComplete={autoComplete}
                minLength={minLength}
                value={valor}
                placeholder={placeholder}
                onChange={(e) => onCambio(e.target.value)}
                required={requerido}
            />
            <button
                type="button"
                className="absolute top-1/2 right-2 -translate-y-1/2 inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold text-royal hover:bg-ice"
                onClick={() => setVisible((v) => !v)}
                aria-label={visible ? 'Ocultar contraseña' : 'Ver contraseña'}
            >
                {visible ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
            </button>
        </div>
    );
}

export function capturar(error: unknown, onError: (mensaje: string) => void): void {
    if (error instanceof ApiError) {
        onError(error.detail);
        return;
    }

    onError(error instanceof Error ? error.message : 'Error inesperado');
}
