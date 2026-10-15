import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type RoleItem, type SharedData, type User } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Usuarios y roles', href: '/usuarios' }];

interface Props {
    usuarios: User[];
    roles: RoleItem[];
}

export default function UsuariosIndex({ usuarios, roles }: Props) {
    const { flash } = usePage<SharedData>().props;
    const form = useForm({
        name: '',
        email: '',
        role: 'consultor',
        cargo: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post('/usuarios', { onSuccess: () => form.reset() });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Usuarios y roles" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Usuarios y roles</h1>
                    <p className="text-sm text-muted-foreground">Administrador, responsable documental, especialista/analista y usuario consultor.</p>
                    {flash?.success && <p className="mt-2 text-sm text-emerald-700">{flash.success}</p>}
                </div>

                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    {roles.map((rol) => (
                        <article key={rol.id} className="rounded-2xl border border-mist bg-white p-4">
                            <h2 className="font-semibold text-navy">{rol.nombre}</h2>
                            <p className="mt-1 text-xs text-slate-600">{rol.descripcion}</p>
                        </article>
                    ))}
                </div>

                <div className="grid gap-4 lg:grid-cols-[1.4fr_0.8fr]">
                    <div className="overflow-hidden rounded-2xl border border-mist bg-white">
                        <table className="w-full text-sm">
                            <thead className="bg-ice text-left">
                                <tr>
                                    <th className="px-4 py-3">Nombre</th>
                                    <th className="px-4 py-3">Correo</th>
                                    <th className="px-4 py-3">Rol</th>
                                    <th className="px-4 py-3">Cargo</th>
                                </tr>
                            </thead>
                            <tbody>
                                {usuarios.map((usuario) => (
                                    <tr key={usuario.id} className="border-t border-mist">
                                        <td className="px-4 py-3 font-medium">{usuario.name}</td>
                                        <td className="px-4 py-3">{usuario.email}</td>
                                        <td className="px-4 py-3">
                                            <StatusBadge value={usuario.role ?? ''} />
                                        </td>
                                        <td className="px-4 py-3">{usuario.cargo}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <form onSubmit={submit} className="space-y-3 rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-lg font-bold text-navy">Nuevo usuario</h2>
                        <div className="grid gap-1">
                            <Label>Nombre</Label>
                            <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                        </div>
                        <div className="grid gap-1">
                            <Label>Correo</Label>
                            <Input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
                        </div>
                        <div className="grid gap-1">
                            <Label>Rol</Label>
                            <select className="h-10 rounded-md border px-3 text-sm" value={form.data.role} onChange={(e) => form.setData('role', e.target.value)}>
                                {roles.map((rol) => (
                                    <option key={rol.clave} value={rol.clave}>
                                        {rol.nombre}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="grid gap-1">
                            <Label>Cargo</Label>
                            <Input value={form.data.cargo} onChange={(e) => form.setData('cargo', e.target.value)} />
                        </div>
                        <Button type="submit" className="w-full bg-royal hover:bg-navy" disabled={form.processing}>
                            Crear usuario
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
