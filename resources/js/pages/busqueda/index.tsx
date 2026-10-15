import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Documento, type Expediente } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Búsqueda', href: '/busqueda' }];

interface Props {
    q: string;
    documentos: Documento[];
    expedientes: Expediente[];
}

export default function BusquedaIndex({ q, documentos, expedientes }: Props) {
    const form = useForm({ q });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.get('/busqueda', { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Búsqueda" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Búsqueda</h1>
                    <p className="text-sm text-muted-foreground">Busca en códigos, títulos y texto extraído. Pruebe «requisitos» o «artículo 12».</p>
                </div>
                <form onSubmit={submit} className="flex gap-2">
                    <Input value={form.data.q} onChange={(e) => form.setData('q', e.target.value)} placeholder="Buscar en el acervo…" className="max-w-xl bg-white" />
                    <Button type="submit" className="bg-royal hover:bg-navy">
                        Buscar
                    </Button>
                </form>

                {q && (
                    <p className="text-sm text-muted-foreground">
                        {documentos.length + expedientes.length} resultado(s) para “{q}”
                    </p>
                )}

                <div className="grid gap-4 lg:grid-cols-2">
                    <section className="rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-lg font-bold text-navy">Documentos</h2>
                        <ul className="mt-3 space-y-3">
                            {documentos.map((documento) => (
                                <li key={documento.id} className="rounded-xl border border-mist p-3">
                                    <Link href={`/documentos/${documento.id}`} className="font-medium text-royal hover:underline">
                                        {documento.titulo}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">{documento.codigo}</p>
                                    <div className="mt-2">
                                        <StatusBadge value={documento.ocr_estado} />
                                    </div>
                                </li>
                            ))}
                            {q && documentos.length === 0 && <p className="text-sm text-muted-foreground">Sin documentos.</p>}
                        </ul>
                    </section>
                    <section className="rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-lg font-bold text-navy">Expedientes</h2>
                        <ul className="mt-3 space-y-3">
                            {expedientes.map((expediente) => (
                                <li key={expediente.id} className="rounded-xl border border-mist p-3">
                                    <Link href={`/expedientes/${expediente.id}`} className="font-medium text-royal hover:underline">
                                        {expediente.codigo}
                                    </Link>
                                    <p className="text-sm">{expediente.titulo}</p>
                                </li>
                            ))}
                            {q && expedientes.length === 0 && <p className="text-sm text-muted-foreground">Sin expedientes.</p>}
                        </ul>
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}
