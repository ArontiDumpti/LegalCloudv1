import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

const estilos: Record<string, string> = {
    abierto: 'bg-ice text-navy border-mist',
    en_revision: 'bg-amber-50 text-amber-800 border-amber-200',
    cerrado: 'bg-slate-100 text-slate-600 border-slate-200',
    alta: 'bg-blue-100 text-royal border-blue-200',
    media: 'bg-ice text-navy border-mist',
    baja: 'bg-white text-slate-600 border-slate-200',
    procesado: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    pendiente: 'bg-amber-50 text-amber-800 border-amber-200',
    indexado: 'bg-ice text-royal border-mist',
    error: 'bg-red-50 text-red-700 border-red-200',
};

const etiquetas: Record<string, string> = {
    abierto: 'Abierto',
    en_revision: 'En revisión',
    cerrado: 'Cerrado',
    alta: 'Alta',
    media: 'Media',
    baja: 'Baja',
    procesado: 'Procesado',
    pendiente: 'Pendiente',
    indexado: 'Indexado',
    error: 'Error',
    administrador: 'Administrador',
    responsable_documental: 'Responsable documental',
    especialista: 'Especialista / Analista',
    consultor: 'Usuario consultor',
};

export function StatusBadge({ value }: { value: string }) {
    return (
        <Badge variant="outline" className={cn('font-medium', estilos[value] ?? 'bg-ice text-navy')}>
            {etiquetas[value] ?? value}
        </Badge>
    );
}
