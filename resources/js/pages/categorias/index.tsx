import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Categoria, type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Categorías', href: '/categorias' }];

export default function CategoriasIndex({ categorias }: { categorias: Categoria[] }) {
    const { flash } = usePage<SharedData>().props;
    const form = useForm({ nombre: '', descripcion: '', color: '#1D4ED8' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post('/categorias', { onSuccess: () => form.reset() });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categorías" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Categorías</h1>
                    <p className="text-sm text-muted-foreground">Clasificación del acervo normativo y administrativo.</p>
                    {flash?.success && <p className="mt-2 text-sm text-emerald-700">{flash.success}</p>}
                </div>
                <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="grid gap-3 sm:grid-cols-2">
                        {categorias.map((categoria) => (
                            <article key={categoria.id} className="rounded-2xl border border-mist bg-white p-5">
                                <span className="inline-block size-3 rounded-full" style={{ backgroundColor: categoria.color }} />
                                <h2 className="mt-2 font-semibold text-navy">{categoria.nombre}</h2>
                                <p className="text-sm text-slate-600">{categoria.descripcion}</p>
                                <p className="mt-3 text-xs text-muted-foreground">
                                    {categoria.expedientes_count ?? 0} expedientes · {categoria.documentos_count ?? 0} documentos
                                </p>
                            </article>
                        ))}
                    </div>
                    <form onSubmit={submit} className="space-y-3 rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-lg font-bold text-navy">Nueva categoría</h2>
                        <div className="grid gap-1">
                            <Label>Nombre</Label>
                            <Input value={form.data.nombre} onChange={(e) => form.setData('nombre', e.target.value)} required />
                        </div>
                        <div className="grid gap-1">
                            <Label>Descripción</Label>
                            <textarea className="min-h-20 rounded-md border px-3 py-2 text-sm" value={form.data.descripcion} onChange={(e) => form.setData('descripcion', e.target.value)} />
                        </div>
                        <div className="grid gap-1">
                            <Label>Color</Label>
                            <Input type="color" value={form.data.color} onChange={(e) => form.setData('color', e.target.value)} className="h-10 w-20 p-1" />
                        </div>
                        <Button type="submit" className="w-full bg-royal hover:bg-navy" disabled={form.processing}>
                            Crear
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
