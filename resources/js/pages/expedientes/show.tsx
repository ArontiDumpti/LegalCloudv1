import { StatusBadge } from '@/components/status-badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Expediente } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Props {
    expediente: Expediente;
}

export default function ExpedienteShow({ expediente }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Expedientes', href: '/expedientes' },
        { title: expediente.codigo, href: `/expedientes/${expediente.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={expediente.codigo} />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div className="rounded-2xl border border-mist bg-white p-6">
                    <p className="text-xs font-semibold tracking-wide text-royal uppercase">{expediente.codigo}</p>
                    <h1 className="font-display mt-1 text-3xl font-bold text-navy">{expediente.titulo}</h1>
                    <p className="mt-2 max-w-3xl text-sm text-slate-600">{expediente.descripcion}</p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <StatusBadge value={expediente.estado} />
                        <StatusBadge value={expediente.prioridad} />
                        {expediente.categoria && <span className="rounded-full bg-ice px-3 py-1 text-xs text-navy">{expediente.categoria.nombre}</span>}
                    </div>
                    <p className="mt-4 text-sm text-muted-foreground">
                        Responsable: {expediente.responsable?.name ?? 'Sin asignar'} · Apertura:{' '}
                        {expediente.fecha_apertura ? new Date(expediente.fecha_apertura).toLocaleDateString('es') : '—'}
                    </p>
                </div>

                <section className="rounded-2xl border border-mist bg-white p-6">
                    <h2 className="font-display text-xl font-bold text-navy">Documentos del expediente</h2>
                    <ul className="mt-4 divide-y divide-mist">
                        {(expediente.documentos ?? []).map((documento) => (
                            <li key={documento.id} className="flex items-center justify-between py-3">
                                <div>
                                    <Link href={`/documentos/${documento.id}`} className="font-medium text-royal hover:underline">
                                        {documento.titulo}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">
                                        {documento.codigo} · v{documento.version_actual}
                                    </p>
                                </div>
                                <StatusBadge value={documento.ocr_estado} />
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </AppLayout>
    );
}
