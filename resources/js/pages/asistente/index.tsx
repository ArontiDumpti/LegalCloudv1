import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type ConsultaRag } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Asistente', href: '/asistente' }];

interface Props {
    consultas: ConsultaRag[];
    sugeridas: string[];
}

export default function AsistenteIndex({ consultas, sugeridas }: Props) {
    const form = useForm({ pregunta: '' });

    const enviar = (pregunta: string) => {
        form.setData('pregunta', pregunta);
        router.post('/asistente', { pregunta }, { onSuccess: () => form.reset() });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        enviar(form.data.pregunta);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Asistente" />
            <div className="flex flex-1 flex-col gap-6 bg-gradient-to-b from-white to-ice p-4 md:p-6">
                <div>
                    <h1 className="font-display text-3xl font-bold text-navy">Asistente</h1>
                    <p className="text-sm text-muted-foreground">Pregunte sobre los documentos indexados.</p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {sugeridas.map((pregunta) => (
                        <button
                            key={pregunta}
                            type="button"
                            onClick={() => enviar(pregunta)}
                            className="rounded-full border border-mist bg-white px-3 py-1.5 text-left text-xs text-navy hover:border-sky"
                        >
                            {pregunta}
                        </button>
                    ))}
                </div>

                <div className="flex min-h-[28rem] flex-col rounded-2xl border border-mist bg-white">
                    <div className="flex-1 space-y-4 overflow-y-auto p-5">
                        {consultas.length === 0 && (
                            <p className="text-sm text-muted-foreground">Aún no hay consultas. Use la pregunta de ejemplo sobre requisitos del procedimiento.</p>
                        )}
                        {consultas.map((consulta) => (
                            <div key={consulta.id} className="space-y-3">
                                <div className="ml-auto max-w-[85%] rounded-2xl bg-navy px-4 py-3 text-sm text-white">{consulta.pregunta}</div>
                                <div className="max-w-[90%] rounded-2xl bg-ice px-4 py-3 text-sm whitespace-pre-wrap text-slate-700">{consulta.respuesta}</div>
                                {(consulta.fuentes ?? []).length > 0 && (
                                    <div className="grid gap-2">
                                        {(consulta.fuentes ?? []).map((fuente, index) => (
                                            <a
                                                key={`${consulta.id}-${index}`}
                                                href={`/documentos/${fuente.documento_id}`}
                                                className="rounded-xl border border-mist bg-white px-3 py-2 text-xs hover:border-sky"
                                            >
                                                <p className="font-semibold text-royal">
                                                    Fuente {index + 1}: {fuente.codigo} · {fuente.titulo}
                                                </p>
                                                <p className="mt-1 line-clamp-3 text-slate-600">{fuente.texto}</p>
                                            </a>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                    <form onSubmit={submit} className="flex gap-2 border-t border-mist p-4">
                        <input
                            className="h-11 flex-1 rounded-full border border-input px-4 text-sm"
                            placeholder="Escriba una pregunta sobre el acervo…"
                            value={form.data.pregunta}
                            onChange={(e) => form.setData('pregunta', e.target.value)}
                            required
                        />
                        <Button type="submit" className="rounded-full bg-royal hover:bg-navy" disabled={form.processing}>
                            Preguntar
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
