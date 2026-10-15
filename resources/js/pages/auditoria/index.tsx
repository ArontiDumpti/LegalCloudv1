import AppLayout from '@/layouts/app-layout';
import { type AuditoriaEvento, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Auditoría', href: '/auditoria' }];

export default function AuditoriaIndex({ eventos }: { eventos: AuditoriaEvento[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Auditoría" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Auditoría</h1>
                    <p className="text-sm text-muted-foreground">Trazabilidad de documentos, consultas y operaciones de los usuarios.</p>
                </div>
                <div className="overflow-hidden rounded-2xl border border-mist bg-white">
                    <table className="w-full text-sm">
                        <thead className="bg-ice text-left">
                            <tr>
                                <th className="px-4 py-3">Fecha</th>
                                <th className="px-4 py-3">Usuario</th>
                                <th className="px-4 py-3">Módulo</th>
                                <th className="px-4 py-3">Acción</th>
                                <th className="px-4 py-3">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            {eventos.map((evento) => (
                                <tr key={evento.id} className="border-t border-mist">
                                    <td className="px-4 py-3 whitespace-nowrap">{new Date(evento.created_at).toLocaleString('es')}</td>
                                    <td className="px-4 py-3">{evento.user?.name ?? 'Sistema'}</td>
                                    <td className="px-4 py-3">{evento.modulo}</td>
                                    <td className="px-4 py-3 font-medium text-navy">{evento.accion}</td>
                                    <td className="px-4 py-3 text-slate-600">{evento.descripcion}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
