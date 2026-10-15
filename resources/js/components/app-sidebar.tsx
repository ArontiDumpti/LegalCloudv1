import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Bell,
    FolderOpen,
    FileText,
    LayoutDashboard,
    MessageSquareText,
    ScanText,
    Search,
    ShieldCheck,
    Tags,
    Users,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', url: '/dashboard', icon: LayoutDashboard },
    { title: 'Expedientes', url: '/expedientes', icon: FolderOpen },
    { title: 'Documentos', url: '/documentos', icon: FileText },
    { title: 'Categorías', url: '/categorias', icon: Tags },
    { title: 'Búsqueda', url: '/busqueda', icon: Search },
    { title: 'OCR', url: '/ocr', icon: ScanText },
    { title: 'Asistente RAG', url: '/asistente', icon: MessageSquareText },
    { title: 'Notificaciones', url: '/notificaciones', icon: Bell },
    { title: 'Usuarios y roles', url: '/usuarios', icon: Users },
    { title: 'Auditoría', url: '/auditoria', icon: ShieldCheck },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const puedeUsuariosAuditoria = usePage<SharedData>().props.auth.puede_usuarios_auditoria ?? false;
    const items = mainNavItems.filter((item) => {
        if (item.url === '/usuarios' || item.url === '/auditoria') {
            return puedeUsuariosAuditoria;
        }

        return true;
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} />
            </SidebarContent>

            <SidebarFooter>
                {footerNavItems.length > 0 && <NavFooter items={footerNavItems} className="mt-auto" />}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
