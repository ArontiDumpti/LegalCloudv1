import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';

const roles: Record<string, string> = {
    administrador: 'Administrador',
    responsable_documental: 'Responsable documental',
    especialista: 'Especialista',
    consultor: 'Consultor',
};

export function UserInfo({ user, showEmail = false }: { user: User; showEmail?: boolean }) {
    const getInitials = useInitials();

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={user.avatar} alt={user.name} />
                <AvatarFallback className="rounded-lg bg-sky/20 text-navy">{getInitials(user.name)}</AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                <span className="truncate text-xs text-sky">{showEmail ? user.email : roles[user.role ?? ''] ?? user.role}</span>
            </div>
        </>
    );
}
