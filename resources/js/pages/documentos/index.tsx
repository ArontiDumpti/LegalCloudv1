import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Categoria, type Documento, type Expediente, type SharedData } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Documentos', href: '/documentos' }];

interface Props {
    documentos: Documento[];
    expedientes: Pick<Expediente, 'id' | 'codigo' | 'titulo'>[];
    categorias: Categoria[];
    filtros: { q?: string };
    ejemplo_pdf: string;
}

export default function DocumentosIndex({ documentos, expedientes, categorias, filtros, ejemplo_pdf }: Props) {
    const { flash } = usePage<SharedData>().props;
    const filtro = useForm({ q: filtros.q ?? '' });
    const crear = useForm({
        expediente_id: String(expedientes[0]?.id ?? ''),
        categoria_id: '',
        titulo: 'Reglamento General de Trámites Administrativos',
        tipo: 'reglamento',
        descripcion: '',
        archivo: null as File | null,
    });

    const buscar: FormEventHandler = (e) => {
        e.preventDefault();
        filtro.get('/documentos', { preserveState: true });
    };

    const guardar: FormEventHandler = (e) => {
        e.preventDefault();
        crear.post('/documentos', {
            forceFormData: true,
            onSuccess: () => crear.reset('titulo', 'descripcion', 'archivo'),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Documentos" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Documentos</h1>
                    <p className="text-sm text-muted-foreground">Suba un PDF. El sistema extrae el texto, lo indexa y lo deja listo para el asistente.</p>
                    {flash?.success && <p className="mt-2 text-sm text-emerald-700">{flash.success}</p>}
                </div>

                <form onSubmit={buscar} className="flex gap-2">
                    <Input value={filtro.data.q} onChange={(e) => filtro.setData('q', e.target.value)} placeholder="Buscar en título, código o texto OCR" className="max-w-md bg-white" />
                    <Button type="submit">Buscar</Button>
                </form>

                <div className="grid gap-4 lg:grid-cols-[1.3fr_0.9fr]">
                    <div className="space-y-3">
                        {documentos.map((documento) => (
                            <Link key={documento.id} href={`/documentos/${documento.id}`} className="block rounded-2xl border border-mist bg-white p-5 hover:border-sky">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold text-royal">{documento.codigo}</p>
                                        <h2 className="font-semibold text-navy">{documento.titulo}</h2>
                                        <p className="text-xs text-muted-foreground">
                                            {documento.expediente?.codigo} · {documento.tipo} · {documento.archivo_nombre ?? 'sin archivo'} · v{documento.version_actual}
                                        </p>
                                    </div>
                                    <div className="flex gap-2">
                                        <StatusBadge value={documento.ocr_estado} />
                                        <StatusBadge value={documento.embedding_estado} />
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>

                    <form onSubmit={guardar} className="space-y-3 rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-lg font-bold text-navy">Subir PDF</h2>
                        <p className="text-sm text-slate-600">Para probarlo ahora, pulse el botón azul. No hace falta elegir archivo.</p>
                        <Button
                            type="button"
                            className="w-full bg-royal hover:bg-navy"
                            onClick={() => router.post('/documentos/ejemplo')}
                        >
                            Subir el PDF de ejemplo ahora
                        </Button>
                        <a href={ejemplo_pdf} className="block text-center text-xs font-semibold text-royal hover:underline" download>
                            O descargar el PDF y cargarlo abajo
                        </a>
                        <div className="border-t border-mist pt-3 text-xs text-muted-foreground">Carga manual</div>
                        <div className="grid gap-1">
                            <Label>Expediente</Label>
                            <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.expediente_id} onChange={(e) => crear.setData('expediente_id', e.target.value)}>
                                {expedientes.map((expediente) => (
                                    <option key={expediente.id} value={expediente.id}>
                                        {expediente.codigo} — {expediente.titulo}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="grid gap-1">
                            <Label>Título</Label>
                            <Input value={crear.data.titulo} onChange={(e) => crear.setData('titulo', e.target.value)} required placeholder="Reglamento General de Trámites…" />
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div className="grid gap-1">
                                <Label>Tipo</Label>
                                <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.tipo} onChange={(e) => crear.setData('tipo', e.target.value)}>
                                    <option value="reglamento">Reglamento</option>
                                    <option value="resolucion">Resolución</option>
                                    <option value="circular">Circular</option>
                                    <option value="contrato">Contrato</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div className="grid gap-1">
                                <Label>Categoría</Label>
                                <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.categoria_id} onChange={(e) => crear.setData('categoria_id', e.target.value)}>
                                    <option value="">—</option>
                                    {categorias.map((categoria) => (
                                        <option key={categoria.id} value={categoria.id}>
                                            {categoria.nombre}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div className="grid gap-1">
                            <Label>Archivo PDF</Label>
                            <Input
                                type="file"
                                accept="application/pdf,.pdf,.txt"
                                required
                                onChange={(e) => crear.setData('archivo', e.target.files?.[0] ?? null)}
                            />
                        </div>
                        {crear.errors.titulo && <p className="text-xs text-red-600">{crear.errors.titulo}</p>}
                        {crear.errors.archivo && <p className="text-xs text-red-600">{crear.errors.archivo}</p>}
                        <Button type="submit" className="w-full" variant="outline" disabled={crear.processing}>
                            Subir el archivo elegido
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
