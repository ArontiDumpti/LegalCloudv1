import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    const { notificaciones_count, auth } = usePage<SharedData>().props;

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-mist bg-white px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <div className="flex items-center gap-3 text-sm">
                <span className="hidden text-muted-foreground md:inline">{auth.role_label}</span>
                <Link href="/notificaciones" className="relative rounded-full border border-mist p-2 text-navy hover:bg-ice">
                    <Bell className="size-4" />
                    {(notificaciones_count ?? 0) > 0 && (
                        <span className="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-royal px-1 text-[10px] font-bold text-white">
                            {notificaciones_count}
                        </span>
                    )}
                </Link>
            </div>
        </header>
    );
}
