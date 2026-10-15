import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Categoria, type Expediente, type SharedData, type User } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Expedientes', href: '/expedientes' }];

interface Props {
    expedientes: Expediente[];
    categorias: Categoria[];
    responsables: User[];
    filtros: { q?: string; estado?: string };
}

export default function ExpedientesIndex({ expedientes, categorias, responsables, filtros }: Props) {
    const { flash } = usePage<SharedData>().props;
    const filtro = useForm({ q: filtros.q ?? '', estado: filtros.estado ?? '' });
    const crear = useForm({
        titulo: '',
        descripcion: '',
        estado: 'abierto',
        prioridad: 'media',
        categoria_id: '',
        responsable_id: '',
    });

    const buscar: FormEventHandler = (e) => {
        e.preventDefault();
        filtro.get('/expedientes', { preserveState: true });
    };

    const guardar: FormEventHandler = (e) => {
        e.preventDefault();
        crear.post('/expedientes', { onSuccess: () => crear.reset() });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Expedientes" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Expedientes</h1>
                    <p className="text-sm text-muted-foreground">Registro y seguimiento del estado de cada carpeta documental.</p>
                    {flash?.success && <p className="mt-2 text-sm text-emerald-700">{flash.success}</p>}
                </div>

                <form onSubmit={buscar} className="flex flex-wrap gap-2">
                    <Input value={filtro.data.q} onChange={(e) => filtro.setData('q', e.target.value)} placeholder="Buscar por código o título" className="max-w-sm bg-white" />
                    <select
                        value={filtro.data.estado}
                        onChange={(e) => filtro.setData('estado', e.target.value)}
                        className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                    >
                        <option value="">Todos los estados</option>
                        <option value="abierto">Abierto</option>
                        <option value="en_revision">En revisión</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                    <Button type="submit">Filtrar</Button>
                </form>

                <div className="grid gap-4 lg:grid-cols-[1.4fr_0.8fr]">
                    <div className="overflow-hidden rounded-2xl border border-mist bg-white">
                        <table className="w-full text-sm">
                            <thead className="bg-ice text-left text-navy">
                                <tr>
                                    <th className="px-4 py-3">Código</th>
                                    <th className="px-4 py-3">Título</th>
                                    <th className="px-4 py-3">Estado</th>
                                    <th className="px-4 py-3">Docs</th>
                                </tr>
                            </thead>
                            <tbody>
                                {expedientes.map((expediente) => (
                                    <tr key={expediente.id} className="border-t border-mist">
                                        <td className="px-4 py-3 font-medium">
                                            <Link href={`/expedientes/${expediente.id}`} className="text-royal hover:underline">
                                                {expediente.codigo}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">{expediente.titulo}</td>
                                        <td className="px-4 py-3">
                                            <StatusBadge value={expediente.estado} />
                                        </td>
                                        <td className="px-4 py-3">{expediente.documentos?.length ?? 0}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <form onSubmit={guardar} className="space-y-3 rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-lg font-bold text-navy">Nuevo expediente</h2>
                        <div className="grid gap-1">
                            <Label>Título</Label>
                            <Input value={crear.data.titulo} onChange={(e) => crear.setData('titulo', e.target.value)} required />
                        </div>
                        <div className="grid gap-1">
                            <Label>Descripción</Label>
                            <textarea
                                className="min-h-20 rounded-md border border-input px-3 py-2 text-sm"
                                value={crear.data.descripcion}
                                onChange={(e) => crear.setData('descripcion', e.target.value)}
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div className="grid gap-1">
                                <Label>Estado</Label>
                                <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.estado} onChange={(e) => crear.setData('estado', e.target.value)}>
                                    <option value="abierto">Abierto</option>
                                    <option value="en_revision">En revisión</option>
                                    <option value="cerrado">Cerrado</option>
                                </select>
                            </div>
                            <div className="grid gap-1">
                                <Label>Prioridad</Label>
                                <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.prioridad} onChange={(e) => crear.setData('prioridad', e.target.value)}>
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div className="grid gap-1">
                            <Label>Categoría</Label>
                            <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.categoria_id} onChange={(e) => crear.setData('categoria_id', e.target.value)}>
                                <option value="">Sin categoría</option>
                                {categorias.map((categoria) => (
                                    <option key={categoria.id} value={categoria.id}>
                                        {categoria.nombre}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="grid gap-1">
                            <Label>Responsable</Label>
                            <select className="h-10 rounded-md border px-3 text-sm" value={crear.data.responsable_id} onChange={(e) => crear.setData('responsable_id', e.target.value)}>
                                <option value="">Sin asignar</option>
                                {responsables.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <Button type="submit" className="w-full bg-royal hover:bg-navy" disabled={crear.processing}>
                            Registrar
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
