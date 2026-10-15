import { ApiError } from './api';

export function ir(path: string): void {
    const destino = path.startsWith('/app') ? path : `/app${path.startsWith('/') ? path : `/${path}`}`;
    window.history.pushState({}, '', destino);
    window.dispatchEvent(new Event('spa:nav'));
}

export function badge(valor: string) {
    return <span className="rounded-full bg-ice px-2 py-0.5 text-[11px] font-semibold text-royal">{valor.replaceAll('_', ' ')}</span>;
}

export function capturar(error: unknown, onError: (mensaje: string) => void): void {
    if (error instanceof ApiError) {
        onError(error.detail);
        return;
    }

    onError(error instanceof Error ? error.message : 'Error inesperado');
}
