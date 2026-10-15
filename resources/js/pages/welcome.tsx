import { Head } from '@inertiajs/react';
import { ArrowRight, FileText, FolderOpen, MessageSquareText, ShieldCheck } from 'lucide-react';

const modules = [
    { title: 'Expedientes', copy: 'Abra, clasifique y dé seguimiento a cada carpeta de trámite.', icon: FolderOpen },
    { title: 'Documentos', copy: 'Cargue archivos, controle versiones y consulte el texto extraído.', icon: FileText },
    { title: 'Asistente', copy: 'Pregunte en lenguaje natural y vea las fuentes de los documentos.', icon: MessageSquareText },
    { title: 'Auditoría', copy: 'Revise quién consultó, cargó o cambió un documento.', icon: ShieldCheck },
];

export default function Welcome() {
    return (
        <>
            <Head title="LegalCloud AI">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|source-serif-4:600,700" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-ice text-navy">
                <header className="sticky top-0 z-10 border-b border-mist/80 bg-white/90 backdrop-blur">
                    <div className="mx-auto flex h-20 max-w-6xl items-center justify-between px-6">
                        <div className="flex items-center gap-2.5">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-navy text-sm font-semibold text-white">LC</span>
                            <div>
                                <p className="text-base leading-none font-semibold">
                                    LegalCloud <span className="text-royal">AI</span>
                                </p>
                                <p className="mt-0.5 text-xs text-slate-500">Gestión documental</p>
                            </div>
                        </div>
                        <a
                            href="/app/login"
                            className="inline-flex items-center gap-2 rounded-xl bg-royal px-6 py-3 text-base font-bold text-white shadow-lg shadow-sky/40 hover:bg-navy"
                        >
                            Iniciar sesión
                            <ArrowRight className="size-5" />
                        </a>
                    </div>
                </header>

                <main className="mx-auto max-w-6xl px-6 py-12 md:py-16">
                    <section className="grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
                        <div>
                            <p className="text-xs font-semibold tracking-[0.18em] text-royal uppercase">Organizaciones jurídicas y administrativas</p>
                            <h1 className="font-display mt-3 text-4xl leading-[1.15] font-bold md:text-5xl">
                                Una sola plataforma para el acervo documental
                            </h1>
                            <p className="mt-4 max-w-xl text-base leading-relaxed text-slate-600">
                                Administre expedientes, documentos y consultas con control de usuarios. El asistente responde con base en los documentos
                                indexados.
                            </p>
                            <div className="mt-8">
                                <a href="#modulos" className="inline-flex items-center rounded-lg border border-mist bg-white px-5 py-2.5 text-sm font-semibold hover:border-sky">
                                    Ver módulos
                                </a>
                            </div>
                            <dl className="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-mist pt-6">
                                <div>
                                    <dt className="text-xs text-slate-500">Expedientes</dt>
                                    <dd className="font-display text-2xl font-bold">Centralizados</dd>
                                </div>
                                <div>
                                    <dt className="text-xs text-slate-500">Consultas</dt>
                                    <dd className="font-display text-2xl font-bold">Con fuentes</dd>
                                </div>
                                <div>
                                    <dt className="text-xs text-slate-500">Acceso</dt>
                                    <dd className="font-display text-2xl font-bold">Por roles</dd>
                                </div>
                            </dl>
                        </div>

                        <div className="overflow-hidden rounded-2xl border border-mist bg-white shadow-xl shadow-sky/15">
                            <div className="flex items-center gap-2 border-b border-mist bg-slate-50 px-4 py-2.5">
                                <span className="size-2.5 rounded-full bg-mist" />
                                <span className="size-2.5 rounded-full bg-mist" />
                                <span className="size-2.5 rounded-full bg-mist" />
                                <span className="ml-2 text-xs text-slate-500">LegalCloud AI</span>
                            </div>
                            <div className="grid grid-cols-[7.5rem_1fr] md:grid-cols-[9rem_1fr]">
                                <aside className="space-y-1 border-r border-mist bg-slate-50 p-3 text-[11px]">
                                    {['Tablero', 'Expedientes', 'Documentos', 'Asistente', 'Auditoría'].map((item, i) => (
                                        <p
                                            key={item}
                                            className={`rounded-md px-2 py-1.5 ${i === 0 ? 'bg-white font-semibold text-royal shadow-sm' : 'text-slate-500'}`}
                                        >
                                            {item}
                                        </p>
                                    ))}
                                </aside>
                                <div className="space-y-3 p-4">
                                    <p className="text-xs font-semibold text-slate-500">Tablero</p>
                                    <div className="grid grid-cols-2 gap-2">
                                        {[
                                            ['Expedientes', '2'],
                                            ['Documentos', '5'],
                                            ['Consultas', '3'],
                                            ['Alertas', '2'],
                                        ].map(([label, value]) => (
                                            <div key={label} className="rounded-xl border border-mist px-3 py-2">
                                                <p className="text-[10px] text-slate-500">{label}</p>
                                                <p className="font-display text-xl font-bold">{value}</p>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="rounded-xl bg-ice px-3 py-3">
                                        <p className="text-[11px] font-semibold text-royal">Asistente</p>
                                        <p className="mt-1 text-xs leading-relaxed text-slate-600">
                                            ¿Cuáles son los requisitos para iniciar un procedimiento administrativo?
                                        </p>
                                        <p className="mt-2 text-[11px] text-slate-500">Respuesta con fuentes · RES-2024-0187</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="modulos" className="mt-16 pb-8">
                        <div className="max-w-2xl">
                            <h2 className="font-display text-2xl font-bold">Qué puede hacer en LegalCloud</h2>
                            <p className="mt-2 text-sm text-slate-600">Los módulos principales de la aplicación, con acceso según el rol de cada usuario.</p>
                        </div>
                        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {modules.map((modulo) => (
                                <article key={modulo.title} className="rounded-2xl border border-mist bg-white p-5 shadow-sm">
                                    <span className="flex size-10 items-center justify-center rounded-xl bg-ice text-royal">
                                        <modulo.icon className="size-5" />
                                    </span>
                                    <h3 className="mt-4 font-semibold">{modulo.title}</h3>
                                    <p className="mt-1 text-sm leading-relaxed text-slate-600">{modulo.copy}</p>
                                </article>
                            ))}
                        </div>
                    </section>
                </main>

                <footer className="border-t border-mist bg-white">
                    <div className="mx-auto flex max-w-6xl items-center px-6 py-5 text-xs text-slate-500">
                        <span>LegalCloud AI</span>
                    </div>
                </footer>
            </div>
        </>
    );
}
