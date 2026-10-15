import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, FileSearch, FolderOpen, MessageSquareText, ScanText, ShieldCheck } from 'lucide-react';

const modules = [
    { title: 'Expedientes', copy: 'Registre, clasifique y siga el estado de cada carpeta.', icon: FolderOpen },
    { title: 'OCR (demo)', copy: 'El texto se extrae de forma simulada para mostrar el flujo.', icon: ScanText },
    { title: 'Asistente RAG', copy: 'Pregunte en lenguaje natural y vea las fuentes recuperadas.', icon: MessageSquareText },
    { title: 'Auditoría', copy: 'Trazabilidad de documentos, consultas y operaciones.', icon: ShieldCheck },
];

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="LegalCloud AI">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|source-serif-4:600,700" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-gradient-to-b from-white via-ice to-white text-navy">
                <header className="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
                    <div className="flex items-center gap-2 font-semibold">
                        <span className="flex size-9 items-center justify-center rounded-lg bg-navy text-sm text-white">LC</span>
                        LegalCloud <span className="text-royal">AI</span>
                    </div>
                    <nav className="flex items-center gap-3 text-sm">
                        <a href="/app/login" className="rounded-full bg-royal px-4 py-2 font-semibold text-white">
                            Frontend API-first
                        </a>
                        {auth.user ? (
                            <Link href={route('dashboard')} className="rounded-full px-4 py-2 text-navy hover:bg-ice">
                                Panel Inertia
                            </Link>
                        ) : (
                            <Link href={route('login')} className="rounded-full px-4 py-2 text-navy hover:bg-ice">
                                Login Inertia
                            </Link>
                        )}
                    </nav>
                </header>

                <section className="hero-grid mx-auto grid max-w-6xl gap-10 px-6 py-16 lg:grid-cols-2 lg:items-center">
                    <div>
                        <p className="text-xs font-semibold tracking-[0.22em] text-royal uppercase">Frontend API-first · OpenAPI v1</p>
                        <h1 className="font-display mt-3 text-4xl leading-tight font-bold md:text-5xl">Gestión documental inteligente para organizaciones jurídicas</h1>
                        <p className="mt-4 max-w-xl text-base text-slate-600">
                            El cliente en /app consume únicamente /api/v1 según openapi/legalcloud-ai.v1.yaml: JWT, RBAC, expedientes,
                            documentos, búsqueda, asistente RAG simulado, usuarios y auditoría. El OCR y el RAG no llaman a un modelo real.
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <a href="/app/login" className="inline-flex items-center gap-2 rounded-full bg-navy px-5 py-2.5 text-sm font-semibold text-white">
                                Abrir frontend API
                                <ArrowRight className="size-4" />
                            </a>
                            <a href="#modulos" className="inline-flex items-center gap-2 rounded-full border border-mist bg-white px-5 py-2.5 text-sm font-semibold">
                                Módulos
                            </a>
                        </div>
                        <p className="mt-4 text-xs text-slate-500">Demo: consultor@legalcloud.com / password</p>
                    </div>
                    <article className="rounded-3xl border border-mist bg-white p-6 shadow-xl shadow-sky/10">
                        <p className="text-xs font-semibold tracking-wide text-royal uppercase">Pregunta de ejemplo</p>
                        <p className="mt-3 rounded-2xl bg-ice px-4 py-3 text-sm">¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?</p>
                        <p className="mt-4 text-sm leading-relaxed text-slate-600">
                            El asistente recupera el <strong>Reglamento General de Trámites Administrativos (RES-2024-0187)</strong> y lista los siete
                            requisitos del artículo 12, mostrando las fuentes que sustentan la respuesta.
                        </p>
                        <div className="mt-5 flex items-center gap-2 text-xs text-royal">
                            <FileSearch className="size-4" />
                            Fuente: EXP-2024-0187 · DOC-RES-2024-0187
                        </div>
                    </article>
                </section>

                <section id="modulos" className="mx-auto max-w-6xl px-6 pb-20">
                    <h2 className="font-display text-2xl font-bold">Módulos principales</h2>
                    <div className="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {modules.map((modulo) => (
                            <article key={modulo.title} className="rounded-2xl border border-mist bg-white p-5">
                                <modulo.icon className="size-5 text-royal" />
                                <h3 className="mt-3 font-semibold">{modulo.title}</h3>
                                <p className="mt-1 text-sm text-slate-600">{modulo.copy}</p>
                            </article>
                        ))}
                    </div>
                </section>
            </div>
        </>
    );
}
