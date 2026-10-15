import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type NotificacionItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Notificaciones', href: '/notificaciones' }];

export default function NotificacionesIndex({ notificaciones }: { notificaciones: NotificacionItem[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notificaciones" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Notificaciones</h1>
                    <p className="text-sm text-muted-foreground">Avisos de carga, OCR y consultas sobre el acervo.</p>
                </div>
                <ul className="space-y-3">
                    {notificaciones.map((item) => (
                        <li key={item.id} className={`rounded-2xl border p-5 ${item.leida ? 'border-mist bg-white' : 'border-sky bg-ice'}`}>
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <h2 className="font-semibold text-navy">{item.titulo}</h2>
                                    <p className="text-sm text-slate-600">{item.mensaje}</p>
                                    {item.enlace && (
                                        <Link href={item.enlace} className="mt-2 inline-block text-sm text-royal hover:underline">
                                            Abrir
                                        </Link>
                                    )}
                                </div>
                                {!item.leida && (
                                    <button className="text-xs font-semibold text-royal" onClick={() => router.post(`/notificaciones/${item.id}/leer`)}>
                                        Marcar leída
                                    </button>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    );
}
