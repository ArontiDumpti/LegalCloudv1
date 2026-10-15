import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Documento, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'OCR', href: '/ocr' }];

export default function OcrIndex({ documentos }: { documentos: Documento[] }) {
    const { flash } = usePage<SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="OCR" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">OCR</h1>
                    <p className="text-sm text-muted-foreground">
                        Simulación del flujo de extracción de texto. El reglamento de ejemplo ya está procesado; el formulario de licencia queda pendiente.
                    </p>
                    {flash?.success && <p className="mt-2 text-sm text-emerald-700">{flash.success}</p>}
                </div>
                <div className="overflow-hidden rounded-2xl border border-mist bg-white">
                    <table className="w-full text-sm">
                        <thead className="bg-ice text-left">
                            <tr>
                                <th className="px-4 py-3">Documento</th>
                                <th className="px-4 py-3">Expediente</th>
                                <th className="px-4 py-3">OCR</th>
                                <th className="px-4 py-3">Embeddings</th>
                                <th className="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {documentos.map((documento) => (
                                <tr key={documento.id} className="border-t border-mist">
                                    <td className="px-4 py-3">
                                        <Link href={`/documentos/${documento.id}`} className="font-medium text-royal hover:underline">
                                            {documento.titulo}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">{documento.codigo}</p>
                                    </td>
                                    <td className="px-4 py-3">{documento.expediente?.codigo}</td>
                                    <td className="px-4 py-3">
                                        <StatusBadge value={documento.ocr_estado} />
                                    </td>
                                    <td className="px-4 py-3">
                                        <StatusBadge value={documento.embedding_estado} />
                                    </td>
                                    <td className="px-4 py-3">
                                        {documento.ocr_estado !== 'procesado' && (
                                            <Button size="sm" className="bg-royal hover:bg-navy" onClick={() => router.post(`/ocr/${documento.id}`)}>
                                                Simular OCR
                                            </Button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
