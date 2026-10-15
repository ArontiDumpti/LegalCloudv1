import { StatusBadge } from '@/components/status-badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type ConsultaRag, type Documento, type Expediente, type AuditoriaEvento, type SharedData, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { FileText, FolderOpen, MessageSquareText, ScanText, ShieldCheck, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface Props {
    stats: {
        expedientes: number;
        documentos: number;
        ocr_procesados: number;
        consultas: number;
        usuarios: number;
        alertas: number;
    };
    expedientes: Expediente[];
    documentos: Documento[];
    consultas: ConsultaRag[];
    auditorias: AuditoriaEvento[];
    equipo: User[];
}

export default function Dashboard({ stats, expedientes, documentos, consultas, auditorias, equipo = [] }: Props) {
    const puedeUsuariosAuditoria = usePage<SharedData>().props.auth.puede_usuarios_auditoria ?? false;
    const cards = [
        { label: 'Expedientes', value: stats.expedientes, icon: FolderOpen, href: '/expedientes' },
        { label: 'Documentos', value: stats.documentos, icon: FileText, href: '/documentos' },
        { label: 'OCR procesados', value: stats.ocr_procesados, icon: ScanText, href: '/ocr' },
        { label: 'Consultas RAG', value: stats.consultas, icon: MessageSquareText, href: '/asistente' },
        ...(puedeUsuariosAuditoria ? [{ label: 'Usuarios', value: stats.usuarios, icon: Users, href: '/usuarios' }] : []),
        { label: 'Alertas', value: stats.alertas, icon: ShieldCheck, href: '/notificaciones' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <p className="text-xs font-semibold tracking-[0.2em] text-royal uppercase">LegalCloud AI</p>
                    <h1 className="font-display text-3xl font-bold text-navy">Panel de gestión documental</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Mockup operativo con un reglamento de ejemplo. El OCR y el asistente RAG están simulados.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {cards.map((card) => (
                        <Link key={card.label} href={card.href} className="rounded-2xl border border-mist bg-white p-5 shadow-sm hover:border-sky">
                            <div className="flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">{card.label}</p>
                                <card.icon className="size-4 text-royal" />
                            </div>
                            <p className="mt-3 font-display text-4xl font-bold text-navy">{card.value}</p>
                        </Link>
                    ))}
                </div>

                <article className="rounded-2xl border border-mist bg-white p-5">
                    <div className="flex items-center justify-between">
                        <h2 className="font-display text-xl font-bold text-navy">Equipo / usuarios</h2>
                        {puedeUsuariosAuditoria && (
                            <Link href="/usuarios" className="text-sm text-royal">
                                Ver tablero
                            </Link>
                        )}
                    </div>
                    <ul className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        {equipo.map((persona) => (
                            <li key={persona.id} className="rounded-xl border border-mist px-4 py-3">
                                <p className="font-semibold text-navy">{persona.name}</p>
                                <p className="text-xs text-muted-foreground">{persona.cargo ?? persona.role}</p>
                            </li>
                        ))}
                    </ul>
                </article>

                <div className="grid gap-4 lg:grid-cols-2">
                    <article className="rounded-2xl border border-mist bg-white p-5">
                        <div className="flex items-center justify-between">
                            <h2 className="font-display text-xl font-bold text-navy">Expedientes recientes</h2>
                            <Link href="/expedientes" className="text-sm text-royal">
                                Ver todos
                            </Link>
                        </div>
                        <ul className="mt-4 space-y-3">
                            {expedientes.map((item) => (
                                <li key={item.id} className="flex items-center justify-between rounded-xl border border-mist px-4 py-3">
                                    <div>
                                        <Link href={`/expedientes/${item.id}`} className="font-medium text-navy hover:underline">
                                            {item.codigo}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">{item.titulo}</p>
                                    </div>
                                    <StatusBadge value={item.estado} />
                                </li>
                            ))}
                        </ul>
                    </article>

                    <article className="rounded-2xl border border-mist bg-white p-5">
                        <div className="flex items-center justify-between">
                            <h2 className="font-display text-xl font-bold text-navy">Documentos</h2>
                            <Link href="/documentos" className="text-sm text-royal">
                                Ver todos
                            </Link>
                        </div>
                        <ul className="mt-4 space-y-3">
                            {documentos.map((item) => (
                                <li key={item.id} className="flex items-center justify-between rounded-xl border border-mist px-4 py-3">
                                    <div>
                                        <Link href={`/documentos/${item.id}`} className="font-medium text-navy hover:underline">
                                            {item.titulo}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">{item.codigo}</p>
                                    </div>
                                    <StatusBadge value={item.ocr_estado} />
                                </li>
                            ))}
                        </ul>
                    </article>
                </div>

                <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                    <article className="rounded-2xl border border-mist bg-white p-5">
                        <h2 className="font-display text-xl font-bold text-navy">Últimas consultas al asistente</h2>
                        <ul className="mt-4 space-y-3">
                            {consultas.map((consulta) => (
                                <li key={consulta.id} className="rounded-xl bg-ice px-4 py-3 text-sm">
                                    <p className="font-medium text-navy">{consulta.pregunta}</p>
                                    <p className="mt-1 line-clamp-2 text-slate-600">{consulta.respuesta}</p>
                                </li>
                            ))}
                        </ul>
                        <Link href="/asistente" className="mt-4 inline-block text-sm font-semibold text-royal">
                            Preguntar ahora →
                        </Link>
                    </article>
                    <article className="rounded-2xl border border-mist bg-gradient-to-br from-navy to-royal p-5 text-white">
                        <p className="text-xs tracking-[0.2em] text-sky uppercase">Trazabilidad</p>
                        <h2 className="font-display mt-2 text-2xl font-bold">Bitácora</h2>
                        <ul className="mt-4 space-y-3 text-sm">
                            {auditorias.map((evento) => (
                                <li key={evento.id} className="border-b border-white/10 pb-2">
                                    <p className="font-medium">{evento.accion}</p>
                                    <p className="text-xs text-blue-100">{evento.descripcion}</p>
                                </li>
                            ))}
                        </ul>
                    </article>
                </div>
            </div>
        </AppLayout>
    );
}
