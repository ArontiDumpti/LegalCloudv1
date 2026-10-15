import type { Token } from './types';

const STORAGE = 'legalcloud.api.tokens';
const BASE = '/api/v1';

export class ApiError extends Error {
    status: number;
    detail: string;

    constructor(status: number, title: string, detail: string) {
        super(detail || title);
        this.status = status;
        this.detail = detail;
    }
}

export function leerTokens(): Token | null {
    const raw = localStorage.getItem(STORAGE);
    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw) as Token;
    } catch {
        return null;
    }
}

export function guardarTokens(token: Token | null): void {
    if (!token) {
        localStorage.removeItem(STORAGE);
        return;
    }

    localStorage.setItem(STORAGE, JSON.stringify(token));
}

async function parseError(respuesta: Response): Promise<never> {
    let title = 'Error';
    let detail = `La API respondió ${respuesta.status}.`;

    try {
        const cuerpo = (await respuesta.json()) as { title?: string; detail?: string };
        title = cuerpo.title || title;
        detail = cuerpo.detail || detail;
    } catch {
        // ignore
    }

    throw new ApiError(respuesta.status, title, detail);
}

async function renovarSiHaceFalta(status: number): Promise<boolean> {
    if (status !== 401) {
        return false;
    }

    const actual = leerTokens();
    if (!actual?.refreshToken) {
        return false;
    }

    const respuesta = await fetch(`${BASE}/auth/renovar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ refreshToken: actual.refreshToken }),
    });

    if (!respuesta.ok) {
        guardarTokens(null);
        return false;
    }

    guardarTokens((await respuesta.json()) as Token);
    return true;
}

export async function api<T>(ruta: string, init: RequestInit = {}, reintento = true): Promise<T> {
    const tokens = leerTokens();
    const headers = new Headers(init.headers);
    headers.set('Accept', 'application/json');

    if (init.body && !(init.body instanceof FormData) && !(init.body instanceof ArrayBuffer) && !(init.body instanceof Blob) && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    if (tokens?.accessToken) {
        headers.set('Authorization', `Bearer ${tokens.accessToken}`);
    }

    const respuesta = await fetch(`${BASE}${ruta}`, { ...init, headers });

    if (respuesta.status === 204) {
        return undefined as T;
    }

    if (respuesta.status === 401 && reintento && (await renovarSiHaceFalta(401))) {
        return api<T>(ruta, init, false);
    }

    if (!respuesta.ok) {
        await parseError(respuesta);
    }

    return (await respuesta.json()) as T;
}

export async function apiBinario(url: string, cuerpo: Blob, contentType: string): Promise<void> {
    const respuesta = await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': contentType },
        body: cuerpo,
    });

    if (!respuesta.ok && respuesta.status !== 204) {
        await parseError(respuesta);
    }
}
