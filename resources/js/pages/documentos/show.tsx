import { StatusBadge } from '@/components/status-badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Documento } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Props {
    documento: Documento;
}

export default function DocumentoShow({ documento }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Documentos', href: '/documentos' },
        { title: documento.codigo, href: `/documentos/${documento.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={documento.titulo} />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div className="rounded-2xl border border-mist bg-white p-6">
                    <p className="text-xs font-semibold text-royal">{documento.codigo}</p>
                    <h1 className="font-display mt-1 text-3xl font-bold text-navy">{documento.titulo}</h1>
                    <p className="mt-2 text-sm text-slate-600">{documento.descripcion}</p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <StatusBadge value={documento.ocr_estado} />
                        <StatusBadge value={documento.embedding_estado} />
                        <span className="rounded-full bg-ice px-3 py-1 text-xs">v{documento.version_actual}</span>
                    </div>
                    <p className="mt-3 text-sm text-muted-foreground">
                        Expediente:{' '}
                        {documento.expediente ? (
                            <Link href={`/expedientes/${documento.expediente.id}`} className="text-royal hover:underline">
                                {documento.expediente.codigo}
                            </Link>
                        ) : (
                            '—'
                        )}{' '}
                        · Subido por {documento.autor?.name ?? 'N/D'} · {documento.archivo_nombre}
                    </p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        {documento.archivo_url && (
                            <a href={documento.archivo_url} className="inline-flex rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white" target="_blank" rel="noreferrer">
                                Ver / descargar PDF
                            </a>
                        )}
                        <Link href="/asistente" className="inline-flex rounded-full bg-royal px-4 py-2 text-sm font-semibold text-white">
                            Preguntar al asistente
                        </Link>
                    </div>
                </div>

                <div className="grid gap-4 lg:grid-cols-[1.4fr_0.8fr]">
                    <article className="rounded-2xl border border-mist bg-white p-6">
                        <h2 className="font-display text-xl font-bold text-navy">Vista del PDF</h2>
                        {documento.archivo_url ? (
                            <iframe title={documento.titulo} src={documento.archivo_url} className="mt-4 h-[32rem] w-full rounded-xl border border-mist bg-ice" />
                        ) : (
                            <p className="mt-4 text-sm text-muted-foreground">Este registro no tiene archivo adjunto.</p>
                        )}
                        <h2 className="font-display mt-6 text-xl font-bold text-navy">Texto extraído (OCR)</h2>
                        <pre className="mt-4 max-h-80 overflow-auto whitespace-pre-wrap rounded-xl bg-ice p-4 font-sans text-sm leading-relaxed text-slate-700">
                            {documento.contenido_ocr || 'Este documento aún no tiene texto extraído. Vaya a OCR para simular el proceso.'}
                        </pre>
                    </article>
                    <article className="rounded-2xl border border-mist bg-white p-6">
                        <h2 className="font-display text-xl font-bold text-navy">Versiones</h2>
                        <ul className="mt-4 space-y-3">
                            {(documento.versiones ?? []).map((version) => (
                                <li key={version.id} className="rounded-xl border border-mist px-4 py-3 text-sm">
                                    <p className="font-semibold text-navy">Versión {version.numero}</p>
                                    <p className="text-muted-foreground">{version.resumen_cambios}</p>
                                    <p className="text-xs">{version.autor?.name}</p>
                                </li>
                            ))}
                        </ul>
                    </article>
                </div>
            </div>
        </AppLayout>
    );
}
